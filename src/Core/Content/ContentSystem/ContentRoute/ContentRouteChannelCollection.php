<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\ContentRoute;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<ContentRouteChannelEntity>
 */
#[Package('discovery')]
class ContentRouteChannelCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ContentRouteChannelEntity::class;
    }
}
