<?php declare(strict_types=1);

namespace HeyFrame\Frontend;

use HeyFrame\Core\Framework\Bundle;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Frontend\DependencyInjection\FrontendMigrationReplacementCompilerPass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * @internal
 */
#[Package('framework')]
class Frontend extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $this->buildDefaultConfig($container);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/DependencyInjection'));
        $loader->load('services.xml');
        $loader->load('seo.xml');

        $container->setParameter('frontendRoot', $this->getPath());
        $container->addCompilerPass(new FrontendMigrationReplacementCompilerPass());

    }
}
