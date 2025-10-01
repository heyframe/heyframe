<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Adapter\Kernel;

use Composer\Autoload\ClassLoader;
use Composer\InstalledVersions;
use Doctrine\DBAL\Connection;
use HeyFrame\Core\DevOps\Environment\EnvironmentHelper;
use HeyFrame\Core\Framework\Adapter\Database\MySQLFactory;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\KernelPluginLoader\DbalKernelPluginLoader;
use HeyFrame\Core\Framework\Plugin\KernelPluginLoader\KernelPluginLoader;
use HeyFrame\Core\Kernel;
use HeyFrame\Core\Profiling\Doctrine\ProfilingMiddleware;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * HeyFrame\Core\Framework\Adapter\Kernel\KernelFactory
 *      HeyFrame\Core\Kernel
 *          HeyFrame\Core\Framework\Adapter\Kernel\HttpCacheKernel (http caching)
 *              HeyFrame\Core\Framework\Adapter\Kernel\HttpKernel (runs request transformer)
 *                  HeyFrame\Frontend\Controller\Any
 *
 * @final
 */
#[Package('framework')]
class KernelFactory
{
    /**
     * @var class-string<Kernel>
     */
    public static string $kernelClass = Kernel::class;

    public static function create(
        string $environment,
        bool $debug,
        ClassLoader $classLoader,
        ?KernelPluginLoader $pluginLoader = null,
        ?Connection $connection = null
    ): HttpKernelInterface {
        if (InstalledVersions::isInstalled('heyframe/platform')) {
            $heyframeVersion = InstalledVersions::getVersion('heyframe/platform')
                . '@' . InstalledVersions::getReference('heyframe/platform');
        } else {
            $heyframeVersion = InstalledVersions::getVersion('heyframe/core')
                . '@' . InstalledVersions::getReference('heyframe/core');
        }

        $middlewares = [];
        if ((\PHP_SAPI !== 'cli' || \in_array('--profile', $_SERVER['argv'] ?? [], true))
            && $environment !== 'prod' && InstalledVersions::isInstalled('symfony/doctrine-bridge')) {
            $middlewares = [new ProfilingMiddleware()];
        }

        $connection ??= MySQLFactory::create($middlewares);

        $pluginLoader ??= new DbalKernelPluginLoader($classLoader, null, $connection);

        $cacheId = (string) EnvironmentHelper::getVariable('HEYFRAME_CACHE_ID', '');

        /** @var KernelInterface $kernel */
        $kernel = new static::$kernelClass(
            $environment,
            $debug,
            $pluginLoader,
            $cacheId,
            $heyframeVersion,
            $connection,
            self::getProjectDir()
        );

        return $kernel;
    }

    private static function getProjectDir(): string
    {
        if ($dir = $_ENV['PROJECT_ROOT'] ?? $_SERVER['PROJECT_ROOT'] ?? false) {
            return $dir;
        }

        $r = new \ReflectionClass(self::class);

        /** @var string $dir */
        $dir = $r->getFileName();

        $dir = $rootDir = \dirname($dir);
        while (!\is_dir($dir . '/vendor')) {
            if ($dir === \dirname($dir)) {
                return $rootDir;
            }
            $dir = \dirname($dir);
        }

        return $dir;
    }
}
