<?php

declare(strict_types=1);

namespace HeyFrame\Core\Framework\DependencyInjection\CompilerPass;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Migration\MigrationSource;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

#[Package('framework')]
abstract class AbstractMigrationReplacementCompilerPass implements CompilerPassInterface
{
    private const MAJOR_VERSIONS = ['V6_3', 'V6_4', 'V6_5', 'V6_6', 'V6_7', 'V6_8'];

    public function process(ContainerBuilder $container): void
    {
        $migrationPath = $this->getMigrationPath();

        foreach (self::MAJOR_VERSIONS as $major) {
            $versionedMigrationPath = $migrationPath . '/Migration/' . $major;

            if (\is_dir($versionedMigrationPath)) {
                $migrationSource = $container->getDefinition(MigrationSource::class . '.core.' . $major);
                $migrationSource->addMethodCall('addDirectory', [$versionedMigrationPath, 'HeyFrame\\' . $this->getMigrationNamespacePart() . '\Migration\\' . $major]);
            }
        }
    }

    abstract protected function getMigrationPath(): string;

    abstract protected function getMigrationNamespacePart(): string;
}
