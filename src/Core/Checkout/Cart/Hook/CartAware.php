<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Hook;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Script\Execution\Awareness\ChannelContextAware;

/**
 * @internal Not intended for use in plugins
 * Can be implemented by hooks to provide services with the sales channel context.
 * The services can inject the context beforehand and provide a narrow API to the developer.
 */
#[Package('checkout')]
interface CartAware extends ChannelContextAware
{
    public function getCart(): Cart;
}
