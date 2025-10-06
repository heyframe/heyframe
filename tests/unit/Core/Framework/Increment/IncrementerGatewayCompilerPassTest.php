<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Framework\Increment;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Framework\Increment\AbstractIncrementer;
use HeyFrame\Core\Framework\Increment\ArrayIncrementer;
use HeyFrame\Core\Framework\Increment\IncrementerGatewayCompilerPass;
use HeyFrame\Core\Framework\Increment\MySQLIncrementer;
use HeyFrame\Core\Framework\Increment\RedisIncrementer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @internal
 */
#[CoversClass(IncrementerGatewayCompilerPass::class)]
class IncrementerGatewayCompilerPassTest extends TestCase
{
    public function testProcess(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('heyframe.increment', [
            'user_activity' => [
                'type' => 'mysql',
            ],
            'message_queue' => [
                'type' => 'redis',
                'config' => ['connection' => 'redis_incrementer'],
            ],
            'another_pool' => [
                'type' => 'array',
            ],
        ]);

        $container->register('heyframe.increment.gateway.array', ArrayIncrementer::class)
            ->addArgument('');

        $container->register('heyframe.increment.gateway.mysql', MySQLIncrementer::class)
            ->addArgument('')
            ->addArgument($this->createMock(Connection::class));

        $entityCompilerPass = new IncrementerGatewayCompilerPass();
        $entityCompilerPass->process($container);

        // user_activity pool is registered
        static::assertTrue($container->hasDefinition('heyframe.increment.user_activity.gateway.mysql'));
        $definition = $container->getDefinition('heyframe.increment.user_activity.gateway.mysql');
        static::assertSame(MySQLIncrementer::class, $definition->getClass());
        static::assertTrue($definition->hasTag('heyframe.increment.gateway'));

        // message_queue pool is registered
        static::assertTrue($container->hasDefinition('heyframe.increment.message_queue.redis_adapter'));
        static::assertTrue($container->hasDefinition('heyframe.increment.message_queue.gateway.redis'));
        $definition = $container->getDefinition('heyframe.increment.message_queue.gateway.redis');
        static::assertSame(RedisIncrementer::class, $definition->getClass());
        static::assertTrue($definition->hasTag('heyframe.increment.gateway'));

        // another_pool is registered
        static::assertNotNull($container->hasDefinition('heyframe.increment.message_queue.gateway.redis'));
        $definition = $container->getDefinition('heyframe.increment.message_queue.gateway.redis');
        static::assertSame(RedisIncrementer::class, $definition->getClass());
        static::assertTrue($definition->hasTag('heyframe.increment.gateway'));
    }

    public function testCustomPoolGateway(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('heyframe.increment', ['custom_pool' => ['type' => 'custom_type']]);

        $customGateway = new class extends AbstractIncrementer {
            public function decrement(string $cluster, string $key): void
            {
            }

            public function increment(string $cluster, string $key): void
            {
            }

            /**
             * @return array<string, array<string, mixed>>
             */
            public function list(string $cluster, int $limit = 5, int $offset = 0): array
            {
                return [];
            }

            public function reset(string $cluster, ?string $key = null): void
            {
            }

            public function getPool(): string
            {
                return 'custom-pool';
            }
        };

        $container->setDefinition('heyframe.increment.custom_pool.gateway.custom_type', new Definition($customGateway::class));

        $entityCompilerPass = new IncrementerGatewayCompilerPass();
        $entityCompilerPass->process($container);

        // custom_pool pool is registered
        static::assertTrue($container->hasDefinition('heyframe.increment.custom_pool.gateway.custom_type'));
        $definition = $container->getDefinition('heyframe.increment.custom_pool.gateway.custom_type');
        static::assertSame($customGateway::class, $definition->getClass());
        static::assertTrue($definition->hasTag('heyframe.increment.gateway'));
    }

    public function testInvalidCustomPoolGateway(): void
    {
        static::expectException(\RuntimeException::class);
        $container = new ContainerBuilder();
        $container->setParameter('heyframe.increment', ['custom_pool' => []]);
        $container->setParameter('heyframe.increment.custom_pool.type', 'custom_type');

        $customGateway = new class {
            public function getPool(): string
            {
                return 'custom-pool';
            }
        };

        $container->setDefinition('heyframe.increment.custom_pool.gateway.custom_type', new Definition($customGateway::class));

        $entityCompilerPass = new IncrementerGatewayCompilerPass();
        $entityCompilerPass->process($container);

        // custom_pool pool is registered
        static::assertTrue($container->hasDefinition('heyframe.increment.custom_pool.gateway.custom_type'));
        $definition = $container->getDefinition('heyframe.increment.custom_pool.gateway.custom_type');
        static::assertSame($customGateway::class, $definition->getClass());
        static::assertTrue($definition->hasTag('heyframe.increment.gateway'));
    }

    public function testInvalidType(): void
    {
        static::expectException(\RuntimeException::class);
        static::expectExceptionMessage('Can not find increment gateway for configured type foo of pool custom_pool, expected service id heyframe.increment.custom_pool.gateway.foo can not be found');
        $container = new ContainerBuilder();
        $container->setParameter('heyframe.increment', ['custom_pool' => [
            'type' => 'foo',
        ]]);
        $container->setParameter('heyframe.increment.custom_pool.type', 'invalid');

        $entityCompilerPass = new IncrementerGatewayCompilerPass();
        $entityCompilerPass->process($container);
    }

    public function testInvalidAdapterClass(): void
    {
        static::expectException(\RuntimeException::class);
        static::expectExceptionMessage('Increment gateway with id heyframe.increment.custom_pool.gateway.array, expected service instance of HeyFrame\Core\Framework\Increment\AbstractIncrementer');
        $container = new ContainerBuilder();
        $container->setParameter('heyframe.increment', ['custom_pool' => ['type' => 'array']]);
        $container->setParameter('heyframe.increment.custom_pool.type', 'custom_type');
        $container->setDefinition('heyframe.increment.gateway.array', new Definition(\ArrayObject::class));

        $entityCompilerPass = new IncrementerGatewayCompilerPass();
        $entityCompilerPass->process($container);
    }

    public function testInvalidRedisAdapter(): void
    {
        static::expectException(\RuntimeException::class);
        static::expectExceptionMessage('Can not find increment gateway for configured type redis of pool custom_pool, expected service id heyframe.increment.custom_pool.gateway.redis can not be found');

        $container = new ContainerBuilder();
        $container->setParameter('heyframe.increment', ['custom_pool' => ['type' => 'redis']]);
        $container->setParameter('heyframe.increment.custom_pool.type', 'custom_type');

        $entityCompilerPass = new IncrementerGatewayCompilerPass();
        $entityCompilerPass->process($container);
    }
}
