<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Framework\DependencyInjection\CompilerPass;

use HeyFrame\Core\Framework\DependencyInjection\CompilerPass\FilesystemConfigMigrationCompilerPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 */
#[CoversClass(FilesystemConfigMigrationCompilerPass::class)]
class FilesystemConfigMigrationCompilerPassTest extends TestCase
{
    private ContainerBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new ContainerBuilder();
        $this->builder->addCompilerPass(new FilesystemConfigMigrationCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 0);
        $this->builder->setParameter('heyframe.filesystem.public', []);
        $this->builder->setParameter('heyframe.filesystem.public.type', 'local');
        $this->builder->setParameter('heyframe.filesystem.public.config', []);
        $this->builder->setParameter('heyframe.cdn.url', 'http://test.de');
    }

    public function testConfigMigration(): void
    {
        $this->builder->compile();

        static::assertSame($this->builder->getParameter('heyframe.filesystem.public'), $this->builder->getParameter('heyframe.filesystem.theme'));
        static::assertSame($this->builder->getParameter('heyframe.filesystem.public'), $this->builder->getParameter('heyframe.filesystem.asset'));
        static::assertSame($this->builder->getParameter('heyframe.filesystem.public'), $this->builder->getParameter('heyframe.filesystem.sitemap'));

        static::assertSame($this->builder->getParameter('heyframe.filesystem.public.type'), $this->builder->getParameter('heyframe.filesystem.theme.type'));
        static::assertSame($this->builder->getParameter('heyframe.filesystem.public.type'), $this->builder->getParameter('heyframe.filesystem.asset.type'));
        static::assertSame($this->builder->getParameter('heyframe.filesystem.public.type'), $this->builder->getParameter('heyframe.filesystem.sitemap.type'));

        static::assertSame($this->builder->getParameter('heyframe.filesystem.public.config'), $this->builder->getParameter('heyframe.filesystem.theme.config'));
        static::assertSame($this->builder->getParameter('heyframe.filesystem.public.config'), $this->builder->getParameter('heyframe.filesystem.asset.config'));
        static::assertSame($this->builder->getParameter('heyframe.filesystem.public.config'), $this->builder->getParameter('heyframe.filesystem.sitemap.config'));

        // We cannot inherit them, cause they use always in 6.2 the shop url instead the configured one
        static::assertSame('', $this->builder->getParameter('heyframe.filesystem.theme.url'));
        static::assertSame('', $this->builder->getParameter('heyframe.filesystem.asset.url'));
        static::assertSame('', $this->builder->getParameter('heyframe.filesystem.sitemap.url'));

        static::assertTrue($this->builder->hasParameter('heyframe.filesystem.theme.visibility'));
    }

    public function testSetCustomConfigForTheme(): void
    {
        $this->builder->setParameter('heyframe.filesystem.theme', ['foo' => 'foo']);
        $this->builder->setParameter('heyframe.filesystem.theme.type', 'amazon-s3');
        $this->builder->setParameter('heyframe.filesystem.theme.config', ['test' => 'test']);
        $this->builder->setParameter('heyframe.filesystem.theme.url', 'http://cdn.de');

        $this->builder->compile();

        static::assertNotSame($this->builder->getParameter('heyframe.filesystem.public'), $this->builder->getParameter('heyframe.filesystem.theme'));
        static::assertNotSame($this->builder->getParameter('heyframe.filesystem.public.type'), $this->builder->getParameter('heyframe.filesystem.theme.type'));
        static::assertNotSame($this->builder->getParameter('heyframe.filesystem.public.config'), $this->builder->getParameter('heyframe.filesystem.theme.config'));

        static::assertSame('amazon-s3', $this->builder->getParameter('heyframe.filesystem.theme.type'));
        static::assertSame('http://cdn.de', $this->builder->getParameter('heyframe.filesystem.theme.url'));
        static::assertSame(['test' => 'test'], $this->builder->getParameter('heyframe.filesystem.theme.config'));
        static::assertTrue($this->builder->hasParameter('heyframe.filesystem.theme.visibility'));

        static::assertSame($this->builder->getParameter('heyframe.filesystem.public'), $this->builder->getParameter('heyframe.filesystem.asset'));
        static::assertSame($this->builder->getParameter('heyframe.filesystem.public.type'), $this->builder->getParameter('heyframe.filesystem.asset.type'));
        static::assertSame($this->builder->getParameter('heyframe.filesystem.public.config'), $this->builder->getParameter('heyframe.filesystem.asset.config'));

        static::assertSame($this->builder->getParameter('heyframe.filesystem.public'), $this->builder->getParameter('heyframe.filesystem.sitemap'));
        static::assertSame($this->builder->getParameter('heyframe.filesystem.public.type'), $this->builder->getParameter('heyframe.filesystem.sitemap.type'));
        static::assertSame($this->builder->getParameter('heyframe.filesystem.public.config'), $this->builder->getParameter('heyframe.filesystem.sitemap.config'));
    }
}
