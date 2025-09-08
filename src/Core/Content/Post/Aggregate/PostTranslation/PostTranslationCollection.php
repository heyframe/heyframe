<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Post\Aggregate\PostTranslation;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<PostTranslationEntity>
 */
#[Package('discovery')]
class PostTranslationCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'post_translation_collection';
    }

    protected function getExpectedClass(): string
    {
        return PostTranslationEntity::class;
    }
}
