<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Media\Metadata;

use HeyFrame\Core\Content\Media\File\MediaFile;
use HeyFrame\Core\Content\Media\MediaType\MediaType;
use HeyFrame\Core\Content\Media\Metadata\MetadataLoader\MetadataLoaderInterface;
use HeyFrame\Core\Framework\Log\Package;

#[Package('discovery')]
class MetadataLoader
{
    /**
     * @internal
     *
     * @param MetadataLoaderInterface[] $metadataLoader
     */
    public function __construct(private readonly iterable $metadataLoader)
    {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function loadFromFile(MediaFile $mediaFile, MediaType $mediaType): ?array
    {
        $metaData = [];
        foreach ($this->metadataLoader as $loader) {
            if ($loader->supports($mediaType)) {
                $metaData = $loader->extractMetadata($mediaFile->getFileName());
                break;
            }
        }

        if ($mediaFile->getHash()) {
            $metaData['hash'] = $mediaFile->getHash();
        }

        return $metaData ?: null;
    }
}
