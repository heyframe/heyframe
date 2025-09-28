<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Theme;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Frontend\Theme\FrontendPluginConfiguration\FrontendPluginConfiguration;
use HeyFrame\Frontend\Theme\FrontendPluginConfiguration\FrontendPluginConfigurationCollection;

#[Package('framework')]
interface ThemeCompilerInterface
{
    public function compileTheme(
        string $channelId,
        string $themeId,
        FrontendPluginConfiguration $themeConfig,
        FrontendPluginConfigurationCollection $configurationCollection,
        bool $withAssets,
        Context $context
    ): void;
}
