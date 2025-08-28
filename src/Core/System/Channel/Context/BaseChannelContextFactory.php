<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Context;

use HeyFrame\Core\Checkout\Cart\Delivery\Struct\ShippingLocation;
use HeyFrame\Core\Checkout\Cart\Price\Struct\CartPrice;
use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupCollection;
use HeyFrame\Core\Checkout\Payment\PaymentMethodCollection;
use HeyFrame\Core\Checkout\Payment\PaymentMethodEntity;
use HeyFrame\Core\Checkout\Shipping\ShippingMethodCollection;
use HeyFrame\Core\Checkout\Shipping\ShippingMethodEntity;
use HeyFrame\Core\Content\MeasurementSystem\MeasurementUnits;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\PartialEntity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Pricing\CashRoundingConfig;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Feature;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\Channel\Aggregate\ChannelDomain\ChannelDomainEntity;
use HeyFrame\Core\System\Channel\BaseChannelContext;
use HeyFrame\Core\System\Channel\ChannelCollection;
use HeyFrame\Core\System\Channel\ChannelEntity;
use HeyFrame\Core\System\Channel\ChannelException;
use HeyFrame\Core\System\Country\Aggregate\CountryState\CountryStateCollection;
use HeyFrame\Core\System\Country\Aggregate\CountryState\CountryStateEntity;
use HeyFrame\Core\System\Country\CountryCollection;
use HeyFrame\Core\System\Country\CountryEntity;
use HeyFrame\Core\System\Currency\Aggregate\CurrencyCountryRounding\CurrencyCountryRoundingCollection;
use HeyFrame\Core\System\Currency\Aggregate\CurrencyCountryRounding\CurrencyCountryRoundingEntity;
use HeyFrame\Core\System\Currency\CurrencyCollection;
use HeyFrame\Core\System\Currency\CurrencyEntity;
use HeyFrame\Core\System\Language\LanguageCollection;
use HeyFrame\Core\System\Tax\TaxCollection;

/**
 * @internal
 */
#[Package('framework')]
class BaseChannelContextFactory extends AbstractBaseChannelContextFactory
{
    /**
     * @param EntityRepository<ChannelCollection> $channelRepository
     * @param EntityRepository<CurrencyCollection> $currencyRepository
     * @param EntityRepository<CustomerGroupCollection> $customerGroupRepository
     * @param EntityRepository<CountryCollection> $countryRepository
     * @param EntityRepository<TaxCollection> $taxRepository
     * @param EntityRepository<PaymentMethodCollection> $paymentMethodRepository
     * @param EntityRepository<ShippingMethodCollection> $shippingMethodRepository
     * @param EntityRepository<CountryStateCollection> $countryStateRepository
     * @param EntityRepository<CurrencyCountryRoundingCollection> $currencyCountryRepository
     * @param EntityRepository<EntityCollection<PartialEntity>> $languageRepository
     */
    public function __construct(
        private readonly EntityRepository $channelRepository,
        private readonly EntityRepository $currencyRepository,
        private readonly EntityRepository $customerGroupRepository,
        private readonly EntityRepository $countryRepository,
        private readonly EntityRepository $taxRepository,
        private readonly EntityRepository $paymentMethodRepository,
        private readonly EntityRepository $shippingMethodRepository,
        private readonly EntityRepository $countryStateRepository,
        private readonly EntityRepository $currencyCountryRepository,
        private readonly ContextFactory $contextFactory,
        private readonly EntityRepository $languageRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $options
     */
    public function create(string $channelId, array $options = []): BaseChannelContext
    {
        $context = $this->contextFactory->getContext($channelId, $options);

        $criteria = new Criteria([$channelId]);
        $criteria->setTitle('base-context-factory::sales-channel');
        $criteria->addAssociation('currency');
        $criteria->addAssociation('domains');

        $domainId = \is_string($options[ChannelContextService::DOMAIN_ID] ?? null) ? $options[ChannelContextService::DOMAIN_ID] : null;

        if (!Feature::isActive('v6.8.0.0')) {
            $criteria->getAssociation('languages')
                ->addFilter(new EqualsFilter('id', $context->getLanguageId()))
                ->addAssociation('translationCode')
                ->addAssociation('locale');
        }

        $channel = $this->channelRepository->search($criteria, $context)->getEntities()->get($channelId);
        if (!$channel instanceof ChannelEntity) {
            throw ChannelException::channelNotFound($channelId);
        }

        // load active currency, fallback to shop currency
        $currency = $channel->getCurrency();
        if (\array_key_exists(ChannelContextService::CURRENCY_ID, $options)) {
            $currencyId = $options[ChannelContextService::CURRENCY_ID];
            if (!\is_string($currencyId) || !Uuid::isValid($currencyId)) {
                throw ChannelException::invalidCurrencyId();
            }

            $criteria = new Criteria([$currencyId]);
            $criteria->setTitle('base-context-factory::currency');

            $currency = $this->currencyRepository->search($criteria, $context)->get($currencyId);

            if (!$currency instanceof CurrencyEntity) {
                throw ChannelException::currencyNotFound($currencyId);
            }
        }

        if ($currency === null) {
            throw ChannelException::currencyNotFound($channel->getCurrencyId());
        }

        // load not logged in customer with default shop configuration or with provided checkout scopes
        $shippingLocation = $this->loadShippingLocation($options, $context, $channel);

        $groupId = $channel->getCustomerGroupId();

        $criteria = new Criteria([$channel->getCustomerGroupId()]);
        $criteria->setTitle('base-context-factory::customer-group');

        $customerGroup = $this->customerGroupRepository->search($criteria, $context)->getEntities()->get($groupId);
        if ($customerGroup === null) {
            throw ChannelException::customerGroupNotFound($groupId);
        }

        // loads tax rules based on active customer and delivery address
        $taxRules = $this->getTaxRules($context);

        // detect active payment method, first check if checkout defined other payment method, otherwise validate if customer logged in, at least use shop default
        $payment = $this->getPaymentMethod($options, $context, $channel);

        // detect active delivery method, at first checkout scope, at least shop default method
        $shippingMethod = $this->getShippingMethod($options, $context, $channel);

        [$itemRounding, $totalRounding] = $this->getCashRounding($currency, $shippingLocation, $context);

        $context = new Context(
            $context->getSource(),
            [],
            $currency->getId(),
            $context->getLanguageIdChain(),
            $context->getVersionId(),
            $currency->getFactor(),
            true,
            CartPrice::TAX_STATE_GROSS,
            $itemRounding
        );

        if (!Feature::isActive('v6.8.0.0')) {
            $languageInfo = $this->getLanguageInfoDeprecated($channel->getLanguages(), $context->getLanguageId());
        } else {
            $languageInfo = $this->getLanguageInfo($context);
        }

        $domainId = \is_string($options[ChannelContextService::DOMAIN_ID] ?? null) ? $options[ChannelContextService::DOMAIN_ID] : null;

        return new BaseChannelContext(
            $context,
            $channel,
            $currency,
            $customerGroup,
            $taxRules,
            $payment,
            $shippingMethod,
            $shippingLocation,
            $itemRounding,
            $totalRounding,
            $languageInfo,
            $this->getMeasurementSystemInfo($channel, $domainId),
        );
    }

    private function getTaxRules(Context $context): TaxCollection
    {
        $criteria = new Criteria();
        $criteria->setTitle('base-context-factory::taxes');
        $criteria->addAssociation('rules.type');

        return $this->taxRepository->search($criteria, $context)->getEntities();
    }

    /**
     * @param array<string, mixed> $options
     */
    private function getPaymentMethod(array $options, Context $context, ChannelEntity $channel): PaymentMethodEntity
    {
        $id = $options[ChannelContextService::PAYMENT_METHOD_ID] ?? $channel->getPaymentMethodId();

        $criteria = new Criteria([$id]);
        $criteria->addAssociation('media');
        $criteria->addAssociation('appPaymentMethod');
        $criteria->setTitle('base-context-factory::payment-method');

        $paymentMethod = $this->paymentMethodRepository
            ->search($criteria, $context)
            ->get($id);

        if (!$paymentMethod instanceof PaymentMethodEntity) {
            throw ChannelException::unknownPaymentMethod($id);
        }

        return $paymentMethod;
    }

    /**
     * @param array<string, mixed> $options
     */
    private function getShippingMethod(array $options, Context $context, ChannelEntity $channel): ShippingMethodEntity
    {
        $id = $options[ChannelContextService::SHIPPING_METHOD_ID] ?? $channel->getShippingMethodId();

        $ids = \array_unique(array_filter([$id, $channel->getShippingMethodId()]));

        $criteria = new Criteria($ids);
        $criteria->addAssociation('media');
        $criteria->setTitle('base-context-factory::shipping-method');

        $shippingMethods = $this->shippingMethodRepository->search($criteria, $context)->getEntities();

        $shippingMethod = $shippingMethods->get($id) ?? $shippingMethods->get($channel->getShippingMethodId());
        if ($shippingMethod === null) {
            throw ChannelException::shippingMethodNotFound($id);
        }

        return $shippingMethod;
    }

    /**
     * @param array<string, mixed> $options
     */
    private function loadShippingLocation(array $options, Context $context, ChannelEntity $channel): ShippingLocation
    {
        // allows previewing cart calculation for a specify state for not logged in customers
        if (isset($options[ChannelContextService::COUNTRY_STATE_ID])) {
            $countryStateId = $options[ChannelContextService::COUNTRY_STATE_ID];
            if (!\is_string($countryStateId) || !Uuid::isValid($countryStateId)) {
                throw ChannelException::invalidCountryStateId();
            }

            $criteria = new Criteria([$countryStateId]);
            $criteria->addAssociation('country');

            $criteria->setTitle('base-context-factory::country');

            $state = $this->countryStateRepository->search($criteria, $context)->get($countryStateId);

            if (!$state instanceof CountryStateEntity) {
                throw ChannelException::countryStateNotFound($countryStateId);
            }

            $country = $state->getCountry();
            if (!$country instanceof CountryEntity) {
                throw ChannelException::countryNotFound($state->getCountryId());
            }

            return new ShippingLocation($country, $state, null);
        }

        $countryId = $options[ChannelContextService::COUNTRY_ID] ?? $channel->getCountryId();
        if (!\is_string($countryId) || !Uuid::isValid($countryId)) {
            throw ChannelException::invalidCountryId();
        }

        $criteria = new Criteria([$countryId]);
        $criteria->setTitle('base-context-factory::country');

        $country = $this->countryRepository->search($criteria, $context)->get($countryId);

        if (!$country instanceof CountryEntity) {
            throw ChannelException::countryNotFound($countryId);
        }

        return ShippingLocation::createFromCountry($country);
    }

    /**
     * @return CashRoundingConfig[]
     */
    private function getCashRounding(CurrencyEntity $currency, ShippingLocation $shippingLocation, Context $context): array
    {
        $criteria = new Criteria();
        $criteria->setTitle('base-context-factory::cash-rounding');
        $criteria->setLimit(1);
        $criteria->addFilter(new EqualsFilter('currencyId', $currency->getId()));
        $criteria->addFilter(new EqualsFilter('countryId', $shippingLocation->getCountry()->getId()));

        $countryConfig = $this->currencyCountryRepository->search($criteria, $context)->first();

        if ($countryConfig instanceof CurrencyCountryRoundingEntity) {
            return [$countryConfig->getItemRounding(), $countryConfig->getTotalRounding()];
        }

        return [$currency->getItemRounding(), $currency->getTotalRounding()];
    }

    private function getLanguageInfo(Context $context): LanguageInfo
    {
        $currentLanguageId = $context->getLanguageId();
        $criteria = (new Criteria([$currentLanguageId]))->addFields([
            'name',
            'translationCode.code',
            'locale.code',
        ]);

        $currentLanguage = $this->languageRepository->search($criteria, $context)->getEntities()->get($currentLanguageId);
        if (!$currentLanguage instanceof PartialEntity) {
            throw ChannelException::languageNotFound($currentLanguageId);
        }

        $locale = $currentLanguage->get('translationCode') ?? $currentLanguage->get('locale');
        \assert($locale instanceof PartialEntity, 'At least the localeId is required, so the fallback should never be null');

        return new LanguageInfo(
            $currentLanguage->get('name'),
            $locale->get('code'),
        );
    }

    private function getLanguageInfoDeprecated(?LanguageCollection $languages, string $currentLanguageId): LanguageInfo
    {
        $currentLanguage = $languages?->get($currentLanguageId);
        if ($currentLanguage === null) {
            throw ChannelException::languageNotFound($currentLanguageId);
        }

        $locale = $currentLanguage->getTranslationCode() ?? $currentLanguage->getLocale();
        \assert($locale !== null, 'At least the localeId is required, so the fallback should never be null');

        return new LanguageInfo(
            $currentLanguage->getTranslation('name') ?? $currentLanguage->getName(),
            $locale->getCode(),
        );
    }

    /**
     * @description load active sales channel domain's measurement units, fallback to sales channel measurement units
     */
    private function getMeasurementSystemInfo(ChannelEntity $channelEntity, ?string $domainId): MeasurementUnits
    {
        if ($domainId && $channelEntity->getDomains()?->get($domainId) instanceof ChannelDomainEntity) {
            return $channelEntity->getDomains()->get($domainId)->getMeasurementUnits();
        }

        return $channelEntity->getMeasurementUnits();
    }
}
