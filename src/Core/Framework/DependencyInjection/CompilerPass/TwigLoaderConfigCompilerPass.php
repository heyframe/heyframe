<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DependencyInjection\CompilerPass;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use HeyFrame\Core\Framework\DependencyInjection\DependencyInjectionException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @internal
 */
#[Package('framework')]
class TwigLoaderConfigCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $fileSystemLoader = $container->findDefinition('twig.loader.native_filesystem');

        $bundlesMetadata = $container->getParameter('kernel.bundles_metadata');
        if (!\is_array($bundlesMetadata)) {
            throw DependencyInjectionException::bundlesMetadataIsNotAnArray();
        }

        foreach ($bundlesMetadata as $name => $bundle) {
            $resourcesDirectory = $bundle['path'] . '/Resources';
            $viewDirectory = $resourcesDirectory . '/views';
            $distDirectory = $resourcesDirectory . '/app/frontend/dist';

            if (\is_dir($viewDirectory)) {
                $fileSystemLoader->addMethodCall('addPath', [$viewDirectory]);
                $fileSystemLoader->addMethodCall('addPath', [$viewDirectory, $name]);
            }

            if (\is_dir($distDirectory)) {
                $fileSystemLoader->addMethodCall('addPath', [$distDirectory, $name]);
            }

            if (\is_dir($resourcesDirectory)) {
                $fileSystemLoader->addMethodCall('addPath', [$resourcesDirectory, $name]);
            }
        }
    }

}
