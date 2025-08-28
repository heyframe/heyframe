<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Media\Metadata\MetadataLoader;

use HeyFrame\Core\Content\Media\MediaType\MediaType;

interface MetadataLoaderInterface
{
    /**
     * @return array<string, mixed>|null
     */
    public function extractMetadata(string $filePath): ?array;

    public function supports(MediaType $mediaType): bool;
}
