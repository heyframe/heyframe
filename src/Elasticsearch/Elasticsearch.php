<?php declare(strict_types=1);

namespace HeyFrame\Elasticsearch;

use HeyFrame\Core\Framework\Bundle;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Elasticsearch\DependencyInjection\ElasticsearchExtension;
use HeyFrame\Elasticsearch\DependencyInjection\ElasticsearchMigrationCompilerPass;
use HeyFrame\Elasticsearch\Profiler\ElasticsearchProfileCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

/**
 * @internal
 */
#[Package('framework')]
class Elasticsearch extends Bundle
{
    public function getTemplatePriority(): int
    {
        return -1;
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $this->buildDefaultConfig($container);

        $container->addCompilerPass(new ElasticsearchMigrationCompilerPass());

        // Needs to run before the ProfilerPass
        $container->addCompilerPass(new ElasticsearchProfileCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 5000);
    }

    protected function createContainerExtension(): ?ExtensionInterface
    {
        return new ElasticsearchExtension();
    }
}
