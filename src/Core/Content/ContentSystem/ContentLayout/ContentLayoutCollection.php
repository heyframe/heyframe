<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\ContentLayout;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<ContentLayoutEntity>
 */
#[Package('discovery')]
class ContentLayoutCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'content_layout_collection';
    }

    protected function getExpectedClass(): string
    {
        return ContentLayoutEntity::class;
    }
}
