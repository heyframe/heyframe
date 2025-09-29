<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Seo\Channel;

use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * This route can be used to load all seo urls of the authenticated sales channel.
 * With this route it is also possible to send the standard API parameters such as: 'page', 'limit', 'filter', etc.
 */
#[Package('inventory')]
abstract class AbstractSeoUrlRoute
{
    abstract public function getDecorated(): AbstractSeoUrlRoute;

    abstract public function load(Request $request, ChannelContext $context, Criteria $criteria): SeoUrlRouteResponse;
}
