<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Plugin\Context;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Migration\MigrationCollection;
use HeyFrame\Core\Framework\Plugin;

class UpdateContext extends InstallContext
{
    public function __construct(
        Plugin $plugin,
        Context $context,
        string $currentHeyFrameVersion,
        string $currentPluginVersion,
        MigrationCollection $migrationCollection,
        private readonly string $updatePluginVersion
    ) {
        parent::__construct($plugin, $context, $currentHeyFrameVersion, $currentPluginVersion, $migrationCollection);
    }

    public function getUpdatePluginVersion(): string
    {
        return $this->updatePluginVersion;
    }
}
