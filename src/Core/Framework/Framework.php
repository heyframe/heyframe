<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework;

use HeyFrame\Core\Framework\DependencyInjection\FrameworkExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * @internal
 */
class Framework extends Bundle
{
    public function getTemplatePriority(): int
    {
        return -1;
    }

    public function getContainerExtension(): Extension
    {
        return new FrameworkExtension();
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        $container->setParameter('locale', 'zh-CN');
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/DependencyInjection/'));
        $loader->load('services.xml');
        $loader->load('rate-limiter.xml');
        $loader->load('data-abstraction-layer.xml');
        $loader->load('script.xml');
        $loader->load('health.xml');
        $loader->load('plugin.xml');
        $loader->load('scheduled-task.xml');
        $loader->load('increment.xml');
        $loader->load('message-queue.xml');
        $loader->load('telemetry.xml');
        $loader->load('cache.xml');
        $loader->load('acl.xml');
        $loader->load('api.xml');
        $loader->load('filesystem.xml');
        $loader->load('event.xml');
        $loader->load('app.xml');
        $loader->load('language.xml');
    }
}
