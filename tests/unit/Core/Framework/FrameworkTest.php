<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Framework;

use HeyFrame\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use HeyFrame\Core\Framework\Feature\FeatureFlagRegistry;
use HeyFrame\Core\Framework\Framework;
use HeyFrame\Core\System\Channel\Entity\ChannelDefinitionInstanceRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;

/**
 * @internal
 */
#[CoversClass(Framework::class)]
class FrameworkTest extends TestCase
{
    public function testTemplatePriority(): void
    {
        $framework = new Framework();

        static::assertSame(-1, $framework->getTemplatePriority());
    }

    public function testFeatureFlagRegisteredOnBoot(): void
    {
        $container = new Container();
        $registry = $this->createMock(FeatureFlagRegistry::class);
        $registry->expects($this->once())->method('register');

        $container->set(FeatureFlagRegistry::class, $registry);
        $container->set(DefinitionInstanceRegistry::class, $this->createMock(DefinitionInstanceRegistry::class));
        $container->set(ChannelDefinitionInstanceRegistry::class, $this->createMock(ChannelDefinitionInstanceRegistry::class));
        $container->setParameter('kernel.cache_dir', '/tmp');
        $container->setParameter('heyframe.cache.cache_compression', true);
        $container->setParameter('heyframe.cache.cache_compression_method', 'gzip');
        $container->setParameter('kernel.debug', true);
        $container->setParameter('kernel.environment', 'test');
        $framework = new Framework();
        $framework->setContainer($container);

        $framework->boot();
    }
}
