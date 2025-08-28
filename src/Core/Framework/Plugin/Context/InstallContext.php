<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Plugin\Context;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Migration\MigrationCollection;
use HeyFrame\Core\Framework\Plugin;

#[Package('framework')]
class InstallContext
{
    private bool $autoMigrate = true;

    public function __construct(
        private readonly Plugin $plugin,
        private readonly Context $context,
        private readonly string $currentHeyFrameVersion,
        private readonly string $currentPluginVersion,
        private readonly MigrationCollection $migrationCollection
    ) {
    }

    public function getPlugin(): Plugin
    {
        return $this->plugin;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getCurrentHeyFrameVersion(): string
    {
        return $this->currentHeyFrameVersion;
    }

    public function getCurrentPluginVersion(): string
    {
        return $this->currentPluginVersion;
    }

    public function getMigrationCollection(): MigrationCollection
    {
        return $this->migrationCollection;
    }

    public function isAutoMigrate(): bool
    {
        return $this->autoMigrate;
    }

    public function setAutoMigrate(bool $autoMigrate): void
    {
        $this->autoMigrate = $autoMigrate;
    }
}
