<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Manifest;

use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('framework')]
class ManifestFactory
{
    public function createFromXmlFile(string $file): Manifest
    {
        return Manifest::createFromXmlFile($file);
    }
}
