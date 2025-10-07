<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\ContentRoute;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<ContentRouteEntity>
 */
#[Package('discovery')]
class ContentRouteCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'content_route_collection';
    }

    protected function getExpectedClass(): string
    {
        return ContentRouteEntity::class;
    }
}
