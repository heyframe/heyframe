<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Framework\Twig;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;
use HeyFrame\Frontend\Framework\Twig\Components\UxComponentRenderEventListener;
use HeyFrame\Frontend\Theme\ThemeConfigValueAccessor;
use HeyFrame\Frontend\Theme\ThemeScripts;

#[Package('framework')]
class TemplateConfigAccessor
{
    /**
     * @internal
     */
    public function __construct(
        private readonly SystemConfigService $systemConfigService,
        private readonly ThemeConfigValueAccessor $themeConfigAccessor,
        private readonly ThemeScripts $themeScripts,
        private readonly UxComponentRenderEventListener $uxComponentRenderEventListener
    ) {
    }

    /**
     * @return string|bool|array<mixed>|float|int|null
     */
    public function config(string $key, ?string $channelId)
    {
        $static = $this->getStatic();

        if (\array_key_exists($key, $static)) {
            return $static[$key];
        }

        return $this->systemConfigService->get($key, $channelId);
    }

    /**
     * @return string|bool|array<string, mixed>|float|int|null
     */
    public function theme(string $key, ChannelContext $context, ?string $themeId)
    {
        return $this->themeConfigAccessor->get($key, $context, $themeId);
    }

    /**
     * @return array<int, string> $items
     */
    public function scripts(): array
    {
        $scripts = [];

        foreach ($this->themeScripts->getThemeScripts() as $script) {
            if (!str_starts_with($script, 'js/components/')) {
                $scripts[] = $script;
            }
        }

        return $scripts;
    }

    public function componentScripts(): array
    {
        $scripts = [];

        foreach ($this->themeScripts->getThemeScripts() as $script) {
            if (str_starts_with($script, 'js/components/')) {
                $scripts[] = $script;
            }
        }

        return $scripts;
    }

    public function mountedComponentScripts(): array
    {
        $scripts = [];
        $mountedScripts = [];
        $mountedComponents = $this->uxComponentRenderEventListener->getMountedComponents();

        foreach ($mountedComponents as $component) {
            $mountedScripts[] = 'js/components/' . str_replace(':', '/', $component) . '.js';
        }

        foreach ($this->themeScripts->getThemeScripts() as $script) {
            if (str_starts_with($script, 'js/components/') && \in_array($script, $mountedScripts, true)) {
                $scripts[] = $script;
            }
        }

        return $scripts;
    }

    /**
     * @return array<string, int|string|bool> $items
     */
    private function getStatic(): array
    {
        return [
        ];
    }
}
