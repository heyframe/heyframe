<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Media\Aggregate\MediaFolder;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<MediaFolderEntity>
 */
#[Package('discovery')]
class MediaFolderCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'media_folder_collection';
    }

    protected function getExpectedClass(): string
    {
        return MediaFolderEntity::class;
    }
}
