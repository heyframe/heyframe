<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Media\Aggregate\MediaThumbnailSize;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<MediaThumbnailSizeEntity>
 */
#[Package('discovery')]
class MediaThumbnailSizeCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'media_thumbnail_size_collection';
    }

    protected function getExpectedClass(): string
    {
        return MediaThumbnailSizeEntity::class;
    }
}
