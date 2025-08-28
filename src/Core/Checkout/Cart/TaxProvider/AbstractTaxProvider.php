<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\TaxProvider;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\TaxProvider\Struct\TaxProviderResult;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
abstract class AbstractTaxProvider
{
    abstract public function provide(Cart $cart, ChannelContext $context): TaxProviderResult;
}
