<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DependencyInjection\CompilerPass;

use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

#[Package('framework')]
class FilesystemConfigMigrationCompilerPass implements CompilerPassInterface
{
    private const MIGRATED_FS = ['theme', 'asset', 'sitemap'];

    public function process(ContainerBuilder $container): void
    {
        foreach (self::MIGRATED_FS as $fs) {
            $key = \sprintf('heyframe.filesystem.%s', $fs);
            $urlKey = $key . '.url';
            $typeKey = $key . '.type';
            $configKey = $key . '.config';
            $visibilityKey = $key . '.visibility';

            if (!$container->hasParameter($visibilityKey)) {
                $container->setParameter($visibilityKey, '%heyframe.filesystem.public.visibility%');
            }

            if ($container->hasParameter($typeKey)) {
                continue;
            }

            // 6.1 always refers to the main shop url on theme, asset and sitemap.
            $container->setParameter($urlKey, '');
            $container->setParameter($key, '%heyframe.filesystem.public%');
            $container->setParameter($typeKey, '%heyframe.filesystem.public.type%');
            $container->setParameter($configKey, '%heyframe.filesystem.public.config%');
        }

        if (!$container->hasParameter('heyframe.filesystem.public.url')) {
            $container->setParameter('heyframe.filesystem.public.url', '%heyframe.cdn.url%');
        }

        if (!$container->hasParameter('heyframe.filesystem.public.visibility')) {
            $container->setParameter('heyframe.filesystem.public.visibility', 'public');
        }
    }
}
