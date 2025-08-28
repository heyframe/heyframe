<?php declare(strict_types=1);

namespace HeyFrame\Core\System\DependencyInjection\CompilerPass;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\DependencyInjection\DependencyInjectionException;
use HeyFrame\Core\System\NumberRange\ValueGenerator\Pattern\IncrementStorage\IncrementRedisStorage;
use HeyFrame\Core\System\NumberRange\ValueGenerator\Pattern\IncrementStorage\IncrementSqlStorage;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

#[Package('framework')]
class NumberRangeIncrementerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $storage = $container->getParameter('heyframe.number_range.increment_storage');

        switch ($storage) {
            case 'mysql':
                $container->removeDefinition('heyframe.number_range.redis');
                $container->removeDefinition(IncrementRedisStorage::class);
                break;
            case 'redis':
                if ($container->getParameter('heyframe.number_range.config.connection') === null) {
                    throw DependencyInjectionException::redisNotConfiguredForNumberRangeIncrementer();
                }

                $container->removeDefinition(IncrementSqlStorage::class);
                break;
        }
    }
}
