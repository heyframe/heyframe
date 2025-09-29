<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Navigation\Channel;

use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

#[Package('discovery')]
abstract class AbstractNavigationRoute
{
    abstract public function getDecorated(): AbstractNavigationRoute;

    abstract public function load(
        string $activeId,
        string $rootId,
        Request $request,
        ChannelContext $context,
        Criteria $criteria
    ): NavigationRouteResponse;
}
