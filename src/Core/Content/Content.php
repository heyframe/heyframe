<?php declare(strict_types=1);

namespace HeyFrame\Core\Content;

use HeyFrame\Core\Framework\Bundle;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * @internal
 */
#[Package('framework')]
class Content extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/DependencyInjection/'));
        $loader->load('media.xml');
        $loader->load('media_path.xml');
        $loader->load('product.xml');
        $loader->load('rule.xml');
        $loader->load('property.xml');
        $loader->load('flow.xml');
        $loader->load('import_export.xml');
        $loader->load('cms.xml');
        $loader->load('category.xml');
        $loader->load('product_stream.xml');
        $loader->load('landing_page.xml');
    }
}
