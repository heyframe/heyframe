<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Framework\Adapter\Asset;

use HeyFrame\Core\Framework\Adapter\Asset\AssetInstallCommand;
use HeyFrame\Core\Framework\Adapter\Cache\CacheInvalidator;
use HeyFrame\Core\Framework\App\ActiveAppsLoader;
use HeyFrame\Core\Framework\Plugin\KernelPluginLoader\KernelPluginLoader;
use HeyFrame\Core\Framework\Plugin\Util\AssetService;
use HeyFrame\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use HeyFrame\Core\Framework\Util\Filesystem;
use HeyFrame\Core\Test\Stub\App\StaticSourceResolver;
use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 */
class AssetInstallCommandTest extends TestCase
{
    use IntegrationTestBehaviour;

    public function testItInstallsAppAssets(): void
    {
        /** @var FilesystemOperator $filesystem */
        $filesystem = static::getContainer()->get('heyframe.filesystem.asset');
        // make sure that the dir does not exist beforehand
        $filesystem->deleteDirectory('bundles/test');
        $filesystem->delete('asset-manifest.json');

        $fixturePath = __DIR__ . '/../../App/Manifest/_fixtures/test';
        $fixturePath = \realpath($fixturePath);
        static::assertIsString($fixturePath);

        $projectDir = static::getContainer()->getParameter('kernel.project_dir');
        static::assertIsString($projectDir);

        $relativeFixturePath = \ltrim(
            \str_replace($projectDir, '', $fixturePath),
            '/'
        );

        $activeAppsLoaderMock = $this->createMock(ActiveAppsLoader::class);
        $activeAppsLoaderMock->expects($this->once())
            ->method('getActiveApps')
            ->willReturn([
                [
                    'name' => 'test',
                    'path' => $relativeFixturePath,
                    'author' => 'heyframe AG',
                ],
            ]);

        $command = new AssetInstallCommand(
            $this->getKernel(),
            new AssetService(
                $filesystem,
                static::getContainer()->get('heyframe.filesystem.private'),
                static::getContainer()->get('kernel'),
                static::getContainer()->get(KernelPluginLoader::class),
                static::getContainer()->get(CacheInvalidator::class),
                new StaticSourceResolver(['test' => new Filesystem($fixturePath)]),
                static::getContainer()->get('parameter_bag')
            ),
            $activeAppsLoaderMock
        );

        $runner = new CommandTester($command);

        static::assertSame(0, $runner->execute([]));
        static::assertTrue($filesystem->has('bundles/test/asset.txt'));

        $filesystem->deleteDirectory('bundles/test');
        $filesystem->delete('asset-manifest.json');
    }
}
