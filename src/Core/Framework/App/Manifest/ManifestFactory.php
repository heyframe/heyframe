<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Manifest;

/**
 * @internal
 */
class ManifestFactory
{
    public function createFromXmlFile(string $file): Manifest
    {
        return Manifest::createFromXmlFile($file);
    }
}
