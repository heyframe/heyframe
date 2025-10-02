<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Plugin;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\PluginExtractionException;
use HeyFrame\Core\Framework\Plugin\Util\ZipUtils;

/**
 * @internal
 */
#[Package('framework')]
class PluginZipDetector
{
    /**
     * @return PluginManagementService::PLUGIN
     */
    public function detect(string $zipFilePath): string
    {
        try {
            $archive = ZipUtils::openZip($zipFilePath);
        } catch (PluginExtractionException) {
            throw PluginException::noPluginFoundInZip($zipFilePath);
        }

        try {
            return match (true) {
                $this->isPlugin($archive) => PluginManagementService::PLUGIN,
                default => throw PluginException::noPluginFoundInZip($zipFilePath),
            };
        } finally {
            $archive->close();
        }
    }

    public function isPlugin(\ZipArchive $archive): bool
    {
        $entry = $archive->statIndex(0);
        if ($entry === false) {
            return false;
        }

        $pluginName = explode('/', (string) $entry['name'])[0];
        $composerFile = $pluginName . '/composer.json';
        $manifestFile = $pluginName . '/manifest.xml';

        $statComposerFile = $archive->statName($composerFile);
        $statManifestFile = $archive->statName($manifestFile);

        return $statComposerFile !== false && $statManifestFile === false;
    }
}
