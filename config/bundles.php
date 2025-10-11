<?php declare(strict_types=1);

use Composer\InstalledVersions;

$bundles = [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Symfony\UX\TwigComponent\TwigComponentBundle::class => ['all' => true],
    HeyFrame\Core\Profiling\Profiling::class => ['all' => true],
    Symfony\Bundle\DebugBundle\DebugBundle::class => ['dev' => true, 'test' => true],
    HeyFrame\Core\Framework\Framework::class => ['all' => true],
    HeyFrame\Core\System\System::class => ['all' => true],
    HeyFrame\Core\Content\Content::class => ['all' => true],
    HeyFrame\Core\Checkout\Checkout::class => ['all' => true],
    HeyFrame\Core\DevOps\DevOps::class => ['all' => true],
    HeyFrame\Core\Maintenance\Maintenance::class => ['all' => true],
    HeyFrame\Administration\Administration::class => ['all' => true],
    HeyFrame\Frontend\Frontend::class => ['all' => true],
];

if (InstalledVersions::isInstalled('symfony/web-profiler-bundle')) {
    $bundles[Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class] = ['dev' => true, 'test' => true, 'phpstan_dev' => true];
}

return $bundles;
