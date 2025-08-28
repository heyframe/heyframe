<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Media\TypeDetector;

use HeyFrame\Core\Content\Media\File\MediaFile;
use HeyFrame\Core\Content\Media\MediaType\AudioType;
use HeyFrame\Core\Content\Media\MediaType\BinaryType;
use HeyFrame\Core\Content\Media\MediaType\ImageType;
use HeyFrame\Core\Content\Media\MediaType\MediaType;
use HeyFrame\Core\Content\Media\MediaType\VideoType;
use HeyFrame\Core\Framework\Log\Package;

#[Package('discovery')]
class DefaultTypeDetector implements TypeDetectorInterface
{
    public function detect(MediaFile $mediaFile, ?MediaType $previouslyDetectedType): ?MediaType
    {
        if ($previouslyDetectedType !== null) {
            return $previouslyDetectedType;
        }

        $mime = explode('/', $mediaFile->getMimeType());

        return match ($mime[0]) {
            'image' => new ImageType(),
            'video' => new VideoType(),
            'audio' => new AudioType(),
            default => new BinaryType(),
        };
    }
}
