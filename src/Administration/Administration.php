<?php declare(strict_types=1);

namespace HeyFrame\Administration;

use HeyFrame\Administration\DependencyInjection\AdministrationMigrationCompilerPass;
use HeyFrame\Core\Framework\Bundle;
use HeyFrame\Core\Framework\Parameter\AdditionalBundleParameters;
use Pentatrion\ViteBundle\PentatrionViteBundle;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 */
class Administration extends Bundle
{
    public function getTemplatePriority(): int
    {
        return -1;
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $this->buildDefaultConfig($container);

        $container->addCompilerPass(new AdministrationMigrationCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 0);
    }

    public function getAdditionalBundles(AdditionalBundleParameters $parameters): array
    {
        return [
            new PentatrionViteBundle(),
        ];
    }
}
