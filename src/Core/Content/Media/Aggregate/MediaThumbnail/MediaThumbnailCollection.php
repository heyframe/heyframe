<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Media\Aggregate\MediaThumbnail;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<MediaThumbnailEntity>
 */
#[Package('discovery')]
class MediaThumbnailCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'media_thumbnail_collection';
    }

    protected function getExpectedClass(): string
    {
        return MediaThumbnailEntity::class;
    }
}
