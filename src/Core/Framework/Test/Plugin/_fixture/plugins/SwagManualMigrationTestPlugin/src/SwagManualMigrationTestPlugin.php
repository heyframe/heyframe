<?php declare(strict_types=1);

namespace SwagManualMigrationTestPlugin;

use HeyFrame\Core\Framework\Plugin;
use HeyFrame\Core\Framework\Plugin\Context\ActivateContext;
use HeyFrame\Core\Framework\Plugin\Context\DeactivateContext;
use HeyFrame\Core\Framework\Plugin\Context\InstallContext;
use HeyFrame\Core\Framework\Plugin\Context\UninstallContext;
use HeyFrame\Core\Framework\Plugin\Context\UpdateContext;

class SwagManualMigrationTestPlugin extends Plugin
{
    public function install(InstallContext $installContext): void
    {
        $installContext->setAutoMigrate(false);
        $installContext->getMigrationCollection()->migrateInPlace(1);
    }

    public function update(UpdateContext $updateContext): void
    {
        $updateContext->setAutoMigrate(false);
        $updateContext->getMigrationCollection()->migrateDestructiveInPlace(1);
        $updateContext->getMigrationCollection()->migrateInPlace(3);
    }

    public function activate(ActivateContext $activateContext): void
    {
        $activateContext->setAutoMigrate(false);
        $activateContext->getMigrationCollection()->migrateInPlace(2);
    }

    public function deactivate(DeactivateContext $deactivateContext): void
    {
        // nth
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        // nth
    }
}
