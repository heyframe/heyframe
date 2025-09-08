<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Post;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<PostEntity>
 */
#[Package('discovery')]
class PostCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'post_collection';
    }

    protected function getExpectedClass(): string
    {
        return PostEntity::class;
    }
}
