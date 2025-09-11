<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Category\Channel;

use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('discovery')]
abstract class AbstractCategoryListRoute
{
    abstract public function getDecorated(): AbstractCategoryListRoute;

    abstract public function load(Criteria $criteria, ChannelContext $context): CategoryListRouteResponse;
}
