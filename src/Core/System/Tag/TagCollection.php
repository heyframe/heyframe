<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Tag;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<TagEntity>
 */
#[Package('fundamentals@framework')]
class TagCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'tag_collection';
    }

    protected function getExpectedClass(): string
    {
        return TagEntity::class;
    }
}
