<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Theme;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin;
use HeyFrame\Core\Framework\Util\Filesystem;
use HeyFrame\Core\Kernel;
use HeyFrame\Frontend\Theme\FrontendPluginConfiguration\FrontendPluginConfiguration;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * @internal
 */
#[Package('framework')]
class ThemeFilesystemResolver
{
    /**
     * @var list<string>
     */
    private readonly array $bundleNames;

    public function __construct(
        private readonly Kernel $kernel,
    ) {
        // get all bundles + plugin names
        // we need to do this because at boot a plugin might not be active (eg during plugin:activate command) and thus not in `getBundles()`
        // but getPluginInstances does not include local bundles (eg Frontend)
        $this->bundleNames = array_values(array_unique(array_merge(
            array_keys($this->kernel->getBundles()),
            array_map(fn (Plugin $plugin): string => $plugin->getName(), $this->kernel->getPluginLoader()->getPluginInstances()->all())
        )));
    }

    public function getFilesystemForFrontendConfig(FrontendPluginConfiguration $configuration): Filesystem
    {
        try {
            $bundle = $this->kernel->getBundle($configuration->getTechnicalName());
        } catch (\InvalidArgumentException) {
            $bundles = $this->kernel->getPluginLoader()
                ->getPluginInstances()
                ->filter(fn (Plugin $plugin) => $plugin->getName() === $configuration->getTechnicalName())
                ->all();

            $bundle = array_values($bundles)[0];
        }

        \assert($bundle instanceof BundleInterface);

        return new Filesystem($bundle->getPath());
    }
}
