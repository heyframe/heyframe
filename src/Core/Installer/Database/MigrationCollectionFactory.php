<?php declare(strict_types=1);

namespace HeyFrame\Core\Installer\Database;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Migration\MigrationCollectionLoader;
use HeyFrame\Core\Framework\Migration\MigrationRuntime;
use HeyFrame\Core\Framework\Migration\MigrationSource;
use Psr\Log\NullLogger;

/**
 * @internal
 */
#[Package('framework')]
class MigrationCollectionFactory
{
    public function __construct(private readonly string $projectDir)
    {
    }

    public function getMigrationCollectionLoader(Connection $connection): MigrationCollectionLoader
    {
        $nullLogger = new NullLogger();

        return new MigrationCollectionLoader(
            $connection,
            new MigrationRuntime($connection, $nullLogger),
            $nullLogger,
            $this->collect(),
        );
    }

    /**
     * @return list<MigrationSource>
     */
    private function collect(): array
    {
        return [
            new MigrationSource('core', []),
            $this->createMigrationSource('V6_3'),
            $this->createMigrationSource('V6_4'),
            $this->createMigrationSource('V6_5'),
            $this->createMigrationSource('V6_6'),
            $this->createMigrationSource('V6_7'),
        ];
    }

    private function createMigrationSource(string $version): MigrationSource
    {
        if (\is_file($this->projectDir . '/platform/src/Core/schema.sql')) {
            $coreBasePath = $this->projectDir . '/platform/src/Core';
            $storefrontBasePath = $this->projectDir . '/platform/src/Storefront';
            $adminBasePath = $this->projectDir . '/platform/src/Administration';
        } elseif (\is_file($this->projectDir . '/src/Core/schema.sql')) {
            $coreBasePath = $this->projectDir . '/src/Core';
            $storefrontBasePath = $this->projectDir . '/src/Storefront';
            $adminBasePath = $this->projectDir . '/src/Administration';
        } elseif (\is_file($this->projectDir . '/vendor/heyframe/platform/src/Core/schema.sql')) {
            $coreBasePath = $this->projectDir . '/vendor/heyframe/platform/src/Core';
            $storefrontBasePath = $this->projectDir . '/vendor/heyframe/platform/src/Storefront';
            $adminBasePath = $this->projectDir . '/vendor/heyframe/platform/src/Administration';
        } else {
            $coreBasePath = $this->projectDir . '/vendor/heyframe/core';
            $storefrontBasePath = $this->projectDir . '/vendor/heyframe/storefront';
            $adminBasePath = $this->projectDir . '/vendor/heyframe/administration';
        }

        $hasStorefrontMigrations = is_dir($storefrontBasePath);
        $hasAdminMigrations = is_dir($adminBasePath);

        $source = new MigrationSource('core.' . $version, [
            \sprintf('%s/Migration/%s', $coreBasePath, $version) => \sprintf('HeyFrame\\Core\\Migration\\%s', $version),
        ]);

        if ($hasStorefrontMigrations) {
            $source->addDirectory(\sprintf('%s/Migration/%s', $storefrontBasePath, $version), \sprintf('HeyFrame\\Storefront\\Migration\\%s', $version));
        }

        if ($hasAdminMigrations) {
            $source->addDirectory(\sprintf('%s/Migration/%s', $adminBasePath, $version), \sprintf('HeyFrame\\Administration\\Migration\\%s', $version));
        }

        return $source;
    }
}
