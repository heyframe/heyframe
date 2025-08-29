<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Context;

use HeyFrame\Core\Checkout\Cart\Price\Struct\CartPrice;
use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupCollection;
use HeyFrame\Core\Checkout\Payment\PaymentMethodCollection;
use HeyFrame\Core\Checkout\Payment\PaymentMethodEntity;
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
use HeyFrame\Core\System\Channel\BaseChannelContext;
use HeyFrame\Core\System\Channel\ChannelCollection;
use HeyFrame\Core\System\Channel\ChannelEntity;
use HeyFrame\Core\System\Channel\ChannelException;
use HeyFrame\Core\System\Country\CountryCollection;
use HeyFrame\Core\System\Currency\Aggregate\CurrencyCountryRounding\CurrencyCountryRoundingCollection;
use HeyFrame\Core\System\Currency\CurrencyCollection;
use HeyFrame\Core\System\Currency\CurrencyEntity;

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
     * @param EntityRepository<PaymentMethodCollection> $paymentMethodRepository
     * @param EntityRepository<CurrencyCountryRoundingCollection> $currencyCountryRepository
     * @param EntityRepository<EntityCollection<PartialEntity>> $languageRepository
     */
    public function __construct(
        private readonly EntityRepository $channelRepository,
        private readonly EntityRepository $currencyRepository,
        private readonly EntityRepository $customerGroupRepository,
        private readonly EntityRepository $paymentMethodRepository,
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
        $criteria->setTitle('base-context-factory::channel');
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

        $groupId = $channel->getCustomerGroupId();

        $criteria = new Criteria([$channel->getCustomerGroupId()]);
        $criteria->setTitle('base-context-factory::customer-group');

        $customerGroup = $this->customerGroupRepository->search($criteria, $context)->getEntities()->get($groupId);
        if ($customerGroup === null) {
            throw ChannelException::customerGroupNotFound($groupId);
        }

        // detect active payment method, first check if checkout defined other payment method, otherwise validate if customer logged in, at least use shop default
        $payment = $this->getPaymentMethod($options, $context, $channel);

        [$itemRounding, $totalRounding] = $this->getCashRounding($currency);

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

        $languageInfo = $this->getLanguageInfo($context);

        return new BaseChannelContext(
            $context,
            $channel,
            $currency,
            $customerGroup,
            $payment,
            $itemRounding,
            $totalRounding,
            $languageInfo,
        );
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
     * @return CashRoundingConfig[]
     */
    private function getCashRounding(CurrencyEntity $currency): array
    {
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
}
