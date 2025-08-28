<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Channel;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\NoContentResponse;

/**
 * This route can be used to delete the entire cart
 */
#[Package('checkout')]
abstract class AbstractCartDeleteRoute
{
    abstract public function getDecorated(): AbstractCartDeleteRoute;

    abstract public function delete(ChannelContext $context): NoContentResponse;
}
