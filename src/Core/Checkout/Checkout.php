<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout;

use HeyFrame\Core\Checkout\DependencyInjection\CompilerPass\CartStorageCompilerPass;
use HeyFrame\Core\Framework\Bundle;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * @internal
 */
#[Package('checkout')]
class Checkout extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new CartStorageCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 0);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/DependencyInjection/'));
        $loader->load('cart.xml');
        $loader->load('customer.xml');
        $loader->load('order.xml');
        $loader->load('payment.xml');
        $loader->load('rule.xml');
    }
}
