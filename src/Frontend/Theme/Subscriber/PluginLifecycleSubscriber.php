<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Theme\Subscriber;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin;
use HeyFrame\Core\Framework\Plugin\Event\PluginLifecycleEvent;
use HeyFrame\Core\Framework\Plugin\Event\PluginPostActivateEvent;
use HeyFrame\Core\Framework\Plugin\Event\PluginPostDeactivateEvent;
use HeyFrame\Core\Framework\Plugin\Event\PluginPostDeactivationFailedEvent;
use HeyFrame\Core\Framework\Plugin\Event\PluginPostUninstallEvent;
use HeyFrame\Core\Framework\Plugin\Event\PluginPreDeactivateEvent;
use HeyFrame\Core\Framework\Plugin\Event\PluginPreUninstallEvent;
use HeyFrame\Core\Framework\Plugin\Event\PluginPreUpdateEvent;
use HeyFrame\Core\Framework\Plugin\PluginLifecycleService;
use HeyFrame\Frontend\Theme\Exception\InvalidThemeBundleException;
use HeyFrame\Frontend\Theme\Exception\ThemeCompileException;
use HeyFrame\Frontend\Theme\FrontendPluginConfiguration\AbstractFrontendPluginConfigurationFactory;
use HeyFrame\Frontend\Theme\FrontendPluginConfiguration\FrontendPluginConfiguration;
use HeyFrame\Frontend\Theme\FrontendPluginRegistry;
use HeyFrame\Frontend\Theme\ThemeLifecycleHandler;
use HeyFrame\Frontend\Theme\ThemeLifecycleService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('framework')]
class PluginLifecycleSubscriber implements EventSubscriberInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly FrontendPluginRegistry $frontendPluginRegistry,
        private readonly string $projectDirectory,
        private readonly AbstractFrontendPluginConfigurationFactory $pluginConfigurationFactory,
        private readonly ThemeLifecycleHandler $themeLifecycleHandler,
        private readonly ThemeLifecycleService $themeLifecycleService
    ) {
    }

    /**
     * @return array<string, string|array{0: string, 1: int}|list<array{0: string, 1?: int}>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            PluginPostActivateEvent::class => 'pluginPostActivate',
            PluginPreUpdateEvent::class => 'pluginUpdate',
            PluginPreDeactivateEvent::class => 'pluginDeactivateAndUninstall',
            PluginPostDeactivateEvent::class => 'pluginPostDeactivate',
            PluginPostDeactivationFailedEvent::class => 'pluginPostDeactivateFailed',
            PluginPreUninstallEvent::class => 'pluginDeactivateAndUninstall',
            PluginPostUninstallEvent::class => 'pluginPostUninstall',
        ];
    }

    public function pluginPostActivate(PluginPostActivateEvent $event): void
    {
        $this->doPostActivate($event);
    }

    public function pluginPostDeactivateFailed(PluginPostDeactivationFailedEvent $event): void
    {
        $this->doPostActivate($event);
    }

    public function pluginUpdate(PluginPreUpdateEvent $event): void
    {
        if ($this->skipCompile($event->getContext()->getContext())) {
            return;
        }

        $pluginName = $event->getPlugin()->getName();
        $config = $this->frontendPluginRegistry->getConfigurations()->getByTechnicalName($pluginName);

        if (!$config) {
            return;
        }

        $this->themeLifecycleHandler->handleThemeInstallOrUpdate(
            $config,
            $this->frontendPluginRegistry->getConfigurations(),
            $event->getContext()->getContext()
        );
    }

    public function pluginPostDeactivate(PluginPostDeactivateEvent $event): void
    {
        $pluginName = $event->getPlugin()->getName();
        $config = $this->frontendPluginRegistry->getConfigurations()->getByTechnicalName($pluginName);

        if (!$config) {
            return;
        }

        if (
            !$config->hasAdditionalBundles()
            || $this->skipCompile($event->getContext()->getContext())
        ) {
            return;
        }

        $this->themeLifecycleHandler->recompileAllActiveThemes($event->getContext()->getContext());
    }

    public function pluginDeactivateAndUninstall(PluginPreDeactivateEvent|PluginPreUninstallEvent $event): void
    {
        if ($this->skipCompile($event->getContext()->getContext())) {
            return;
        }

        $pluginName = $event->getPlugin()->getName();
        $config = $this->frontendPluginRegistry->getConfigurations()->getByTechnicalName($pluginName);

        if (!$config) {
            return;
        }

        if ($config->hasAdditionalBundles()) {
            $this->themeLifecycleHandler->deactivateTheme($config, $event->getContext()->getContext());

            return;
        }

        $this->themeLifecycleHandler->handleThemeUninstall($config, $event->getContext()->getContext());
    }

    public function pluginPostUninstall(PluginPostUninstallEvent $event): void
    {
        if ($event->getContext()->keepUserData()) {
            return;
        }

        $this->themeLifecycleService->removeTheme($event->getPlugin()->getName(), $event->getContext()->getContext());
    }

    /**
     * @throws ThemeCompileException
     * @throws InvalidThemeBundleException
     */
    private function createConfigFromClassName(string $pluginPath, string $className): FrontendPluginConfiguration
    {
        /** @var Plugin $plugin */
        $plugin = new $className(true, $pluginPath, $this->projectDirectory);

        if (!$plugin instanceof Plugin) {
            throw new \RuntimeException(
                \sprintf('Plugin class "%s" must extend "%s"', $plugin::class, Plugin::class)
            );
        }

        return $this->pluginConfigurationFactory->createFromBundle($plugin);
    }

    private function doPostActivate(PluginLifecycleEvent $event): void
    {
        if (!($event instanceof PluginPostActivateEvent) && !($event instanceof PluginPostDeactivationFailedEvent)) {
            return;
        }

        if ($this->skipCompile($event->getContext()->getContext())) {
            return;
        }

        // create instance of the plugin to create a configuration
        // (the kernel boot is already finished and the activated plugin is missing)
        $frontendPluginConfig = $this->createConfigFromClassName(
            $event->getPlugin()->getPath() ?: '',
            $event->getPlugin()->getBaseClass()
        );

        // ensure plugin configuration is in the list of all active plugin configurations
        $configurationCollection = clone $this->frontendPluginRegistry->getConfigurations();
        $configurationCollection->add($frontendPluginConfig);

        $this->themeLifecycleHandler->handleThemeInstallOrUpdate(
            $frontendPluginConfig,
            $configurationCollection,
            $event->getContext()->getContext()
        );
    }

    private function skipCompile(Context $context): bool
    {
        return $context->hasState(PluginLifecycleService::STATE_SKIP_ASSET_BUILDING);
    }
}
