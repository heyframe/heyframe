<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Tax;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Country\CountryEntity;

#[Package('checkout')]
abstract class AbstractTaxDetector
{
    abstract public function getDecorated(): AbstractTaxDetector;

    abstract public function useGross(ChannelContext $context): bool;

    abstract public function isNetDelivery(ChannelContext $context): bool;

    abstract public function getTaxState(ChannelContext $context): string;

    abstract public function isCompanyTaxFree(ChannelContext $context, CountryEntity $shippingLocationCountry): bool;
}
