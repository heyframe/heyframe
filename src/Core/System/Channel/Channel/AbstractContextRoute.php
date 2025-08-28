<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Channel;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * This route can be used to fetch the current context
 * The context contains information about the logged-in user, selected language, selected address etc.
 */
#[Package('framework')]
abstract class AbstractContextRoute
{
    abstract public function getDecorated(): AbstractContextRoute;

    abstract public function load(ChannelContext $context): ContextLoadRouteResponse;
}
