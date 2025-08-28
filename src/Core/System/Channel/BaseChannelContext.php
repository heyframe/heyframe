<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel;

use HeyFrame\Core\Checkout\Cart\Delivery\Struct\ShippingLocation;
use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupEntity;
use HeyFrame\Core\Checkout\Payment\PaymentMethodEntity;
use HeyFrame\Core\Checkout\Shipping\ShippingMethodEntity;
use HeyFrame\Core\Content\MeasurementSystem\MeasurementUnits;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Pricing\CashRoundingConfig;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\Context\LanguageInfo;
use HeyFrame\Core\System\Currency\CurrencyEntity;
use HeyFrame\Core\System\Tax\TaxCollection;

/**
 * Contains basic customer-independent information of the current sales channel.
 *
 * @internal Use ChannelContext for extensions
 *
 * @codeCoverageIgnore
 */
#[Package('framework')]
class BaseChannelContext
{
    public function __construct(
        protected Context $context,
        protected ChannelEntity $channel,
        protected CurrencyEntity $currency,
        protected CustomerGroupEntity $currentCustomerGroup,
        protected TaxCollection $taxRules,
        protected PaymentMethodEntity $paymentMethod,
        protected ShippingMethodEntity $shippingMethod,
        protected ShippingLocation $shippingLocation,
        private readonly CashRoundingConfig $itemRounding,
        private readonly CashRoundingConfig $totalRounding,
        private readonly LanguageInfo $languageInfo,
        private readonly MeasurementUnits $measurementSystemInfo,
    ) {
    }

    public function getCurrentCustomerGroup(): CustomerGroupEntity
    {
        return $this->currentCustomerGroup;
    }

    public function getCurrencyId(): string
    {
        return $this->currency->getId();
    }

    public function getCurrency(): CurrencyEntity
    {
        return $this->currency;
    }

    public function getChannelId(): string
    {
        return $this->channel->getId();
    }

    public function getChannel(): ChannelEntity
    {
        return $this->channel;
    }

    public function getTaxRules(): TaxCollection
    {
        return $this->taxRules;
    }

    public function getPaymentMethod(): PaymentMethodEntity
    {
        return $this->paymentMethod;
    }

    public function getShippingMethod(): ShippingMethodEntity
    {
        return $this->shippingMethod;
    }

    public function getShippingLocation(): ShippingLocation
    {
        return $this->shippingLocation;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getTaxState(): string
    {
        return $this->context->getTaxState();
    }

    public function getTotalRounding(): CashRoundingConfig
    {
        return $this->totalRounding;
    }

    public function getItemRounding(): CashRoundingConfig
    {
        return $this->itemRounding;
    }

    public function getLanguageInfo(): LanguageInfo
    {
        return $this->languageInfo;
    }

    public function getMeasurementSystemInfo(): MeasurementUnits
    {
        return $this->measurementSystemInfo;
    }

    public function getApiAlias(): string
    {
        return 'base_channel_context';
    }
}
