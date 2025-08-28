<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Media\Aggregate\MediaDefaultFolder;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<MediaDefaultFolderEntity>
 */
#[Package('discovery')]
class MediaDefaultFolderCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'media_default_folder_collection';
    }

    protected function getExpectedClass(): string
    {
        return MediaDefaultFolderEntity::class;
    }
}
