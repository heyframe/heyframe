<?php declare(strict_types=1);

namespace HeyFrame\Core\DevOps\StaticAnalyze\PHPStan;

use HeyFrame\Core\DevOps\StaticAnalyze\StaticAnalyzeKernel;
use HeyFrame\Core\Framework\Adapter\Kernel\KernelFactory;
use HeyFrame\Core\Framework\Plugin\KernelPluginLoader\StaticKernelPluginLoader;
use Symfony\Bundle\FrameworkBundle\Console\Application;

$classLoader = require __DIR__ . '/phpstan-bootstrap.php';

$pluginLoader = new StaticKernelPluginLoader($classLoader);

KernelFactory::$kernelClass = StaticAnalyzeKernel::class;

/** @var StaticAnalyzeKernel $kernel */
$kernel = KernelFactory::create(
    environment: 'phpstan_dev',
    debug: true,
    classLoader: $classLoader,
    pluginLoader: $pluginLoader
);

$kernel->boot();

return new Application($kernel);
