<?php
declare(strict_types=1);

namespace HeyFrame\Core\Framework\Plugin\KernelPluginLoader;

use HeyFrame\Core\Framework\Adapter\Composer\ComposerInfoProvider;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Util\PluginFinder;

/**
 * @phpstan-import-type PluginInfo from KernelPluginLoader
 */
#[Package('framework')]
class ComposerPluginLoader extends KernelPluginLoader
{
    /**
     * @return array<PluginInfo>
     */
    public function fetchPluginInfos(): array
    {
        $this->loadPluginInfos();

        return $this->pluginInfos;
    }

    protected function loadPluginInfos(): void
    {
        $this->pluginInfos = [];

        foreach (ComposerInfoProvider::getComposerPackages(PluginFinder::COMPOSER_TYPE) as $composerPackage) {
            $composerJsonPath = $composerPackage->path . '/composer.json';

            if (!\is_file($composerJsonPath)) {
                continue;
            }

            $composerJsonContent = \file_get_contents($composerJsonPath);
            \assert(\is_string($composerJsonContent));

            $composerJson = \json_decode($composerJsonContent, true, 512, \JSON_THROW_ON_ERROR);
            \assert(\is_array($composerJson));
            $pluginClass = $composerJson['extra']['heyframe-plugin-class'] ?? '';

            if (\defined('\STDERR') && ($pluginClass === '' || !\class_exists($pluginClass))) {
                \fwrite(\STDERR, \sprintf('Skipped package %s due invalid "heyframe-plugin-class" config', $composerPackage->name) . \PHP_EOL);

                continue;
            }

            $nameParts = \explode('\\', (string) $pluginClass);

            $this->pluginInfos[] = [
                'name' => \end($nameParts),
                'baseClass' => $pluginClass,
                'active' => true,
                'path' => $composerPackage->path,
                'version' => $composerPackage->prettyVersion,
                'autoload' => $composerJson['autoload'] ?? [],
                'managedByComposer' => true,
                'composerName' => $composerPackage->name,
            ];
        }
    }
}
