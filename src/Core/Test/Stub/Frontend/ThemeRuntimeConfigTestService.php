<?php declare(strict_types=1);

namespace HeyFrame\Core\Test\Stub\Frontend;

use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Frontend\Theme\FrontendPluginConfiguration\FrontendPluginConfigurationCollection;
use HeyFrame\Frontend\Theme\ThemeRuntimeConfig;
use HeyFrame\Frontend\Theme\ThemeRuntimeConfigService;

/**
 * @internal
 */
class ThemeRuntimeConfigTestService extends ThemeRuntimeConfigService
{
    /**
     * @var array<string, ThemeRuntimeConfig>
     */
    private array $configs = [];

    public function __construct(FrontendPluginConfigurationCollection $configurationCollection)
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
