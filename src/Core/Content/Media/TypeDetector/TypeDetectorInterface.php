<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Media\TypeDetector;

use HeyFrame\Core\Content\Media\File\MediaFile;
use HeyFrame\Core\Content\Media\MediaType\MediaType;

interface TypeDetectorInterface
{
    public function detect(MediaFile $mediaFile, ?MediaType $previouslyDetectedType): ?MediaType;
}
