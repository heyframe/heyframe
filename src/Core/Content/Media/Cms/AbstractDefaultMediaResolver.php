<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Media\Cms;

use HeyFrame\Core\Content\Media\MediaEntity;

abstract class AbstractDefaultMediaResolver
{
    abstract public function getDecorated(): AbstractDefaultMediaResolver;

    abstract public function getDefaultCmsMediaEntity(string $mediaAssetFilePath): ?MediaEntity;
}
