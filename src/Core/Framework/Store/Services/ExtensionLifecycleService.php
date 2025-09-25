<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Store\Services;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Plugin\PluginLifecycleService;
use HeyFrame\Core\Framework\Plugin\PluginManagementService;
use HeyFrame\Core\Framework\Plugin\PluginService;

/**
 * @internal
 */
#[Package('checkout')]
class ExtensionLifecycleService extends AbstractExtensionLifecycle
{
    public function __construct(
        private readonly PluginService $pluginService,
        private readonly PluginLifecycleService $pluginLifecycleService,
        private readonly PluginManagementService $pluginManagementService
    ) {
    }

    public function install(string $type, string $technicalName, Context $context): void
    {
        $plugin = $this->pluginService->getPluginByName($technicalName, $context);
        $this->pluginLifecycleService->installPlugin($plugin, $context);
    }

    public function update(string $type, string $technicalName, bool $allowNewPermissions, Context $context): void
    {
        $plugin = $this->pluginService->getPluginByName($technicalName, $context);
        $this->pluginLifecycleService->updatePlugin($plugin, $context);
    }

    public function uninstall(string $type, string $technicalName, bool $keepUserData, Context $context): void
    {
        $plugin = $this->pluginService->getPluginByName($technicalName, $context);
        $this->pluginLifecycleService->uninstallPlugin($plugin, $context, $keepUserData);
    }

    public function activate(string $type, string $technicalName, Context $context): void
    {
        $plugin = $this->pluginService->getPluginByName($technicalName, $context);
        $this->pluginLifecycleService->activatePlugin($plugin, $context);
    }

    public function deactivate(string $type, string $technicalName, Context $context): void
    {
        $plugin = $this->pluginService->getPluginByName($technicalName, $context);
        $this->pluginLifecycleService->deactivatePlugin($plugin, $context);
    }

    public function remove(string $type, string $technicalName, bool $keepUserData, Context $context): void
    {
        $plugin = $this->pluginService->getPluginByName($technicalName, $context);
        $this->pluginManagementService->deletePlugin($plugin, $context);
    }

    protected function getDecorated(): AbstractExtensionLifecycle
    {
        throw new DecorationPatternException(self::class);
    }
}
