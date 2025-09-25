<?php declare(strict_types=1);

namespace HeyFrame\Core\System;

use HeyFrame\Core\Framework\Bundle;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\DependencyInjection\CompilerPass\ChannelEntityCompilerPass;
use HeyFrame\Core\System\DependencyInjection\CompilerPass\NumberRangeIncrementerCompilerPass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * @internal
 */
#[Package('framework')]
class System extends Bundle
{
    public function getTemplatePriority(): int
    {
        return -1;
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/DependencyInjection/'));
        $loader->load('channel.xml');
        $loader->load('country.xml');
        $loader->load('currency.xml');
        $loader->load('locale.xml');
        $loader->load('snippet.xml');
        $loader->load('user.xml');
        $loader->load('integration.xml');
        $loader->load('state_machine.xml');
        $loader->load('configuration.xml');
        $loader->load('number_range.xml');
        $loader->load('tag.xml');
        $loader->load('dict.xml');

        $container->addCompilerPass(new ChannelEntityCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 0);
        $container->addCompilerPass(new NumberRangeIncrementerCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 0);
    }
}
