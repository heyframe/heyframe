<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\DependencyInjection\CompilerPass;

use HeyFrame\Core\Checkout\Cart\CartPersister;
use HeyFrame\Core\Checkout\Cart\RedisCartPersister;
use HeyFrame\Core\Checkout\DependencyInjection\DependencyInjectionException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 */
#[Package('checkout')]
class CartStorageCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $storage = $container->getParameter('heyframe.cart.storage.type');

        switch ($storage) {
            case 'mysql':
                $container->removeDefinition('heyframe.cart.redis');
                $container->removeDefinition(RedisCartPersister::class);
                break;
            case 'redis':
                if ($container->getParameter('heyframe.cart.storage.config.connection') === null) {
                    throw DependencyInjectionException::redisNotConfiguredForCartStorage();
                }

                $container->removeDefinition(CartPersister::class);
                $container->setAlias(CartPersister::class, RedisCartPersister::class);
                break;
        }
    }
}
