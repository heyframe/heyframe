<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Plugin\Context;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Migration\MigrationCollection;
use HeyFrame\Core\Framework\Plugin;

class UninstallContext extends InstallContext
{
    public function __construct(
        Plugin $plugin,
        Context $context,
        string $currentHeyFrameVersion,
        string $currentPluginVersion,
        MigrationCollection $migrationCollection,
        private readonly bool $keepUserData
    ) {
        parent::__construct($plugin, $context, $currentHeyFrameVersion, $currentPluginVersion, $migrationCollection);
    }

    /**
     * If true is returned, migrations of the plugin will also be removed
     */
    public function keepUserData(): bool
    {
        return $this->keepUserData;
    }
}
