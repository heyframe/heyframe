<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Framework\DependencyInjection\CompilerPass;

use HeyFrame\Core\Framework\DependencyInjection\CompilerPass\BusinessEventRegisterCompilerPass;
use HeyFrame\Core\Framework\Event\BusinessEventRegistry;
use HeyFrame\Core\Framework\Framework;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 */
#[CoversClass(BusinessEventRegisterCompilerPass::class)]
class BusinessEventRegisterCompilerPassTest extends TestCase
{
    public function testEventsGetAdded(): void
    {
        $container = new ContainerBuilder();
        $container->register(BusinessEventRegistry::class)
            ->setPublic(true);

        $container->addCompilerPass(new BusinessEventRegisterCompilerPass([Framework::class]));

        $container->compile();
        static::assertContains(Framework::class, $container->get(BusinessEventRegistry::class)->getClasses());
    }
}
