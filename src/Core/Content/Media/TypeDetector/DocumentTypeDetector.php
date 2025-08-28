<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Media\TypeDetector;

use HeyFrame\Core\Content\Media\File\MediaFile;
use HeyFrame\Core\Content\Media\MediaType\DocumentType;
use HeyFrame\Core\Content\Media\MediaType\MediaType;
use HeyFrame\Core\Framework\Log\Package;

#[Package('discovery')]
class DocumentTypeDetector implements TypeDetectorInterface
{
    protected const SUPPORTED_FILE_EXTENSIONS = [
        'pdf' => [],
        'doc' => [],
        'docx' => [],
        'odt' => [],
    ];

    public function detect(MediaFile $mediaFile, ?MediaType $previouslyDetectedType): ?MediaType
    {
        $fileExtension = mb_strtolower($mediaFile->getFileExtension());
        if (!\array_key_exists($fileExtension, self::SUPPORTED_FILE_EXTENSIONS)) {
            return $previouslyDetectedType;
        }

        if ($previouslyDetectedType === null) {
            $previouslyDetectedType = new DocumentType();
        }

        $previouslyDetectedType->addFlags(self::SUPPORTED_FILE_EXTENSIONS[$fileExtension]);

        return $previouslyDetectedType;
    }
}
