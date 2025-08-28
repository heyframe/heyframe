<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Adapter\Cache;

use HeyFrame\Core\Framework\Adapter\AdapterException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

#[Package('framework')]
class CacheCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $storage = $container->getParameter('heyframe.cache.invalidation.delay_options.storage');

        switch ($storage) {
            case 'mysql':
                $container->removeDefinition('heyframe.cache.invalidator.storage.redis_adapter');
                $container->removeDefinition('heyframe.cache.invalidator.storage.redis');
                break;
            case 'redis':
                if ($container->getParameter('heyframe.cache.invalidation.delay_options.connection') === null) {
                    throw AdapterException::missingRequiredParameter('heyframe.cache.invalidation.delay_options.connection');
                }

                $container->removeDefinition('heyframe.cache.invalidator.storage.mysql');
                break;
        }
    }
}
