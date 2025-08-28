<?php declare(strict_types=1);

namespace HeyFrame\Core\Test\Stub\Storefront;

use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Storefront\Theme\StorefrontPluginConfiguration\StorefrontPluginConfigurationCollection;
use HeyFrame\Storefront\Theme\ThemeRuntimeConfig;
use HeyFrame\Storefront\Theme\ThemeRuntimeConfigService;

/**
 * @internal
 */
class ThemeRuntimeConfigTestService extends ThemeRuntimeConfigService
{
    /**
     * @var array<string, ThemeRuntimeConfig>
     */
    private array $configs = [];

    public function __construct(StorefrontPluginConfigurationCollection $configurationCollection)
    {
        foreach ($configurationCollection as $plugin) {
            if (!$plugin->getIsTheme()) {
                continue;
            }

            $this->configs[$plugin->getTechnicalName()] = ThemeRuntimeConfig::fromArray([
                'themeId' => Uuid::randomHex(),
                'technicalName' => $plugin->getTechnicalName(),
                'viewInheritance' => $plugin->getViewInheritance(),
            ]);
        }
    }

    public function getActiveThemeNames(): array
    {
        return array_keys($this->configs);
    }

    public function getRuntimeConfigByName(string $technicalName): ?ThemeRuntimeConfig
    {
        return $this->configs[$technicalName] ?? null;
    }
}
