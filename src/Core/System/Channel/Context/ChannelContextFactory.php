<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Context;

use HeyFrame\Core\Checkout\Cart\Delivery\Struct\ShippingLocation;
use HeyFrame\Core\Checkout\Cart\Price\Struct\CartPrice;
use HeyFrame\Core\Checkout\Cart\Tax\AbstractTaxDetector;
use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressCollection;
use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupCollection;
use HeyFrame\Core\Checkout\Customer\CustomerCollection;
use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Checkout\Payment\PaymentMethodCollection;
use HeyFrame\Core\Checkout\Payment\PaymentMethodEntity;
use HeyFrame\Core\Framework\Api\Context\ChannelApiSource;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Pricing\CashRoundingConfig;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\System\Channel\BaseChannelContext;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Event\ChannelContextPermissionsChangedEvent;
use HeyFrame\Core\System\Currency\Aggregate\CurrencyCountryRounding\CurrencyCountryRoundingCollection;
use HeyFrame\Core\System\Tax\Aggregate\TaxRule\TaxRuleCollection;
use HeyFrame\Core\System\Tax\Aggregate\TaxRule\TaxRuleEntity;
use HeyFrame\Core\System\Tax\TaxCollection;
use HeyFrame\Core\System\Tax\TaxRuleType\TaxRuleTypeFilterInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[Package('discovery')]
class ChannelContextFactory extends AbstractChannelContextFactory
{
    /**
     * @internal
     *
     * @param EntityRepository<CustomerCollection> $customerRepository
     * @param EntityRepository<CustomerGroupCollection> $customerGroupRepository
     * @param EntityRepository<CustomerAddressCollection> $addressRepository
     * @param EntityRepository<PaymentMethodCollection> $paymentMethodRepository
     * @param iterable<TaxRuleTypeFilterInterface> $taxRuleTypeFilter
     * @param EntityRepository<CurrencyCountryRoundingCollection> $currencyCountryRepository
     */
    public function __construct(
        private readonly EntityRepository $customerRepository,
        private readonly EntityRepository $customerGroupRepository,
        private readonly EntityRepository $addressRepository,
        private readonly EntityRepository $paymentMethodRepository,
        private readonly AbstractTaxDetector $taxDetector,
        private readonly iterable $taxRuleTypeFilter,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly EntityRepository $currencyCountryRepository,
        private readonly AbstractBaseChannelContextFactory $baseChannelContextFactory,
    ) {
    }

    public function getDecorated(): AbstractChannelContextFactory
    {
        throw new DecorationPatternException(self::class);
    }

    public function create(string $token, string $channelId, array $options = []): ChannelContext
    {
        // we split the context generation to allow caching of the base context
        $base = $this->baseChannelContextFactory->create($channelId, $options);

        // customer
        $customer = null;
        if (\is_string($options[ChannelContextService::CUSTOMER_ID] ?? null)) {
            // load logged in customer and set active addresses
            $customer = $this->loadCustomer($options, $base->getContext());
        }

        $shippingLocation = $base->getShippingLocation();
        if ($customer) {
            $activeShippingAddress = $customer->getActiveShippingAddress();
            \assert($activeShippingAddress !== null);
            $shippingLocation = ShippingLocation::createFromAddress($activeShippingAddress);
        }

        $customerGroup = $base->getCurrentCustomerGroup();

        if ($customer) {
            $criteria = new Criteria([$customer->getGroupId()]);
            $criteria->setTitle('context-factory::customer-group');
            $customerGroup = $this->customerGroupRepository->search($criteria, $base->getContext())->getEntities()->first() ?? $customerGroup;
        }

        // loads tax rules based on active customer and delivery address
        $taxRules = $this->getTaxRules($base, $customer, $shippingLocation);

        // detect active payment method, first check if checkout defined other payment method, otherwise validate if customer logged in, at least use shop default
        $payment = $this->getPaymentMethod($options, $base, $customer);

        [$itemRounding, $totalRounding] = $this->getCashRounding($base, $shippingLocation);

        $context = new Context(
            $base->getContext()->getSource(),
            [],
            $base->getCurrencyId(),
            $base->getContext()->getLanguageIdChain(),
            $base->getContext()->getVersionId(),
            $base->getCurrency()->getFactor(),
            true,
            CartPrice::TAX_STATE_GROSS,
            $itemRounding
        );

        $channel = $base->getChannel();

        $domainId = \is_string($options[ChannelContextService::DOMAIN_ID] ?? null) ? $options[ChannelContextService::DOMAIN_ID] : null;

        $channelContext = new ChannelContext(
            $context,
            $token,
            $domainId,
            $channel,
            $base->getCurrency(),
            $customerGroup,
            $taxRules,
            $payment,
            $base->getShippingMethod(),
            $shippingLocation,
            $customer,
            $itemRounding,
            $totalRounding,
            $base->getLanguageInfo(),
        );

        $channelContext->setMeasurementSystem($base->getMeasurementSystemInfo());

        if (\is_array($options[ChannelContextService::PERMISSIONS] ?? null)) {
            $channelContext->setPermissions($options[ChannelContextService::PERMISSIONS]);

            $event = new ChannelContextPermissionsChangedEvent($channelContext, $options[ChannelContextService::PERMISSIONS]);
            $this->eventDispatcher->dispatch($event);

            $channelContext->lockPermissions();
        }

        if (\is_string($options[ChannelContextService::IMITATING_USER_ID] ?? null)) {
            $channelContext->setImitatingUserId($options[ChannelContextService::IMITATING_USER_ID]);
        }

        $channelContext->setTaxState($this->taxDetector->getTaxState($channelContext));

        return $channelContext;
    }

    private function getTaxRules(BaseChannelContext $context, ?CustomerEntity $customer, ShippingLocation $shippingLocation): TaxCollection
    {
        $taxes = $context->getTaxRules()->getElements();

        foreach ($taxes as $tax) {
            $taxRules = $tax->getRules();
            if ($taxRules === null) {
                continue;
            }

            $taxRules = $taxRules->filter(function (TaxRuleEntity $taxRule) use ($customer, $shippingLocation) {
                foreach ($this->taxRuleTypeFilter as $ruleTypeFilter) {
                    if ($ruleTypeFilter->match($taxRule, $customer, $shippingLocation)) {
                        return true;
                    }
                }

                return false;
            });

            $matchingRules = new TaxRuleCollection();
            $taxRule = $taxRules->highestTypePosition();

            if (!$taxRule) {
                $tax->setRules($matchingRules);

                continue;
            }

            $taxRules = $taxRules->filterByTypePosition($taxRule->getType()->getPosition());
            $taxRule = $taxRules->latestActivationDate();

            if ($taxRule) {
                $matchingRules->add($taxRule);
            }
            $tax->setRules($matchingRules);
        }

        return new TaxCollection($taxes);
    }

    /**
     * @codeCoverageIgnore
     *
     * @param array<string, mixed> $options
     */
    private function getPaymentMethod(array $options, BaseChannelContext $context, ?CustomerEntity $customer): PaymentMethodEntity
    {
        if ($customer === null || isset($options[ChannelContextService::PAYMENT_METHOD_ID])) {
            return $context->getPaymentMethod();
        }

        $id = $customer->getLastPaymentMethodId();

        if ($id === null || $id === $context->getPaymentMethod()->getId()) {
            return $context->getPaymentMethod();
        }

        $criteria = new Criteria([$id]);
        $criteria->addAssociation('media');
        $criteria->addAssociation('appPaymentMethod');
        $criteria->setTitle('context-factory::payment-method');
        $criteria->addFilter(new EqualsFilter('active', 1));
        $criteria->addFilter(new EqualsFilter('channels.id', $context->getChannelId()));

        $paymentMethod = $this->paymentMethodRepository->search($criteria, $context->getContext())->getEntities()->get($id);
        if (!$paymentMethod) {
            return $context->getPaymentMethod();
        }

        return $paymentMethod;
    }

    /**
     * @param array<string, mixed> $options
     */
    private function loadCustomer(array $options, Context $context): ?CustomerEntity
    {
        $addressIds = [];
        $customerId = $options[ChannelContextService::CUSTOMER_ID];

        $criteria = new Criteria([$customerId]);
        $criteria->setTitle('context-factory::customer');
        $criteria->addAssociation('salutation');

        $source = $context->getSource();
        \assert($source instanceof ChannelApiSource);

        $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, [
            new EqualsFilter('customer.boundChannelId', null),
            new EqualsFilter('customer.boundChannelId', $source->getChannelId()),
        ]));

        $customer = $this->customerRepository->search($criteria, $context)->getEntities()->get($customerId);
        if (!$customer) {
            return null;
        }

        $activeBillingAddressId = $options[ChannelContextService::BILLING_ADDRESS_ID] ?? $customer->getDefaultBillingAddressId();
        $activeShippingAddressId = $options[ChannelContextService::SHIPPING_ADDRESS_ID] ?? $customer->getDefaultShippingAddressId();

        $addressIds[] = $activeBillingAddressId;
        $addressIds[] = $activeShippingAddressId;
        $addressIds[] = $customer->getDefaultBillingAddressId();
        $addressIds[] = $customer->getDefaultShippingAddressId();

        $criteria = new Criteria(\array_unique($addressIds));
        $criteria->setTitle('context-factory::addresses');
        $criteria->addAssociation('salutation');
        $criteria->addAssociation('country');
        $criteria->addAssociation('countryState');

        $addresses = $this->addressRepository->search($criteria, $context)->getEntities();

        $activeBillingAddress = $addresses->get($activeBillingAddressId) ?? $addresses->get($customer->getDefaultBillingAddressId());
        \assert($activeBillingAddress !== null);
        $customer->setActiveBillingAddress($activeBillingAddress);
        $activeShippingAddress = $addresses->get($activeShippingAddressId) ?? $addresses->get($customer->getDefaultShippingAddressId());
        \assert($activeShippingAddress !== null);
        $customer->setActiveShippingAddress($activeShippingAddress);
        $defaultBillingAddress = $addresses->get($customer->getDefaultBillingAddressId());
        \assert($defaultBillingAddress !== null);
        $customer->setDefaultBillingAddress($defaultBillingAddress);
        $defaultShippingAddress = $addresses->get($customer->getDefaultShippingAddressId());
        \assert($defaultShippingAddress !== null);
        $customer->setDefaultShippingAddress($defaultShippingAddress);

        return $customer;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return array{CashRoundingConfig, CashRoundingConfig}
     */
    private function getCashRounding(BaseChannelContext $context, ShippingLocation $shippingLocation): array
    {
        if ($context->getShippingLocation()->getCountry()->getId() === $shippingLocation->getCountry()->getId()) {
            return [$context->getItemRounding(), $context->getTotalRounding()];
        }

        $criteria = new Criteria();
        $criteria->setTitle('context-factory::cash-rounding');
        $criteria->setLimit(1);
        $criteria->addFilter(new EqualsFilter('currencyId', $context->getCurrencyId()));
        $criteria->addFilter(new EqualsFilter('countryId', $shippingLocation->getCountry()->getId()));

        $countryConfig = $this->currencyCountryRepository
            ->search($criteria, $context->getContext())
            ->getEntities()
            ->first();

        if ($countryConfig) {
            return [$countryConfig->getItemRounding(), $countryConfig->getTotalRounding()];
        }

        return [$context->getCurrency()->getItemRounding(), $context->getCurrency()->getTotalRounding()];
    }
}
