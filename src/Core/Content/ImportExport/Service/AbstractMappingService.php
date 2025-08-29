<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ImportExport\Service;

use HeyFrame\Core\Content\ImportExport\Processing\Mapping\MappingCollection;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Package('fundamentals@after-sales')]
abstract class AbstractMappingService
{
    abstract public function getDecorated(): AbstractMappingService;

    abstract public function createTemplate(Context $context, string $profileId): string;

    abstract public function getMappingFromTemplate(
        Context $context,
        UploadedFile $file,
        string $sourceEntity,
        string $delimiter = ';',
        string $enclosure = '"',
        string $escape = '\\'
    ): MappingCollection;
}
