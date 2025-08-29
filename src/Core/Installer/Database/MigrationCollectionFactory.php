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
            $frontendBasePath = $this->projectDir . '/platform/src/Frontend';
            $adminBasePath = $this->projectDir . '/platform/src/Administration';
        } elseif (\is_file($this->projectDir . '/src/Core/schema.sql')) {
            $coreBasePath = $this->projectDir . '/src/Core';
            $frontendBasePath = $this->projectDir . '/src/Frontend';
            $adminBasePath = $this->projectDir . '/src/Administration';
        } elseif (\is_file($this->projectDir . '/vendor/heyframe/platform/src/Core/schema.sql')) {
            $coreBasePath = $this->projectDir . '/vendor/heyframe/platform/src/Core';
            $frontendBasePath = $this->projectDir . '/vendor/heyframe/platform/src/Frontend';
            $adminBasePath = $this->projectDir . '/vendor/heyframe/platform/src/Administration';
        } else {
            $coreBasePath = $this->projectDir . '/vendor/heyframe/core';
            $frontendBasePath = $this->projectDir . '/vendor/heyframe/frontend';
            $adminBasePath = $this->projectDir . '/vendor/heyframe/administration';
        }

        $hasFrontendMigrations = is_dir($frontendBasePath);
        $hasAdminMigrations = is_dir($adminBasePath);

        $source = new MigrationSource('core.' . $version, [
            \sprintf('%s/Migration/%s', $coreBasePath, $version) => \sprintf('HeyFrame\\Core\\Migration\\%s', $version),
        ]);

        if ($hasFrontendMigrations) {
            $source->addDirectory(\sprintf('%s/Migration/%s', $frontendBasePath, $version), \sprintf('HeyFrame\\Frontend\\Migration\\%s', $version));
        }

        if ($hasAdminMigrations) {
            $source->addDirectory(\sprintf('%s/Migration/%s', $adminBasePath, $version), \sprintf('HeyFrame\\Administration\\Migration\\%s', $version));
        }

        return $source;
    }
}
