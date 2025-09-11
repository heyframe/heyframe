<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Snippet\Struct;

use GuzzleHttp\Psr7\Uri;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin;
use HeyFrame\Core\Framework\Struct\Struct;
use HeyFrame\Core\System\Snippet\DataTransfer\Language\LanguageCollection;
use HeyFrame\Core\System\Snippet\DataTransfer\PluginMapping\PluginMappingCollection;

#[Package('discovery')]
class TranslationConfig extends Struct
{
    /**
     * @param list<string> $locales
     * @param list<string> $plugins
     * @param list<string> $excludedLocales
     *
     * @internal
     */
    public function __construct(
        public readonly Uri $repositoryUrl,
        public readonly array $locales,
        public readonly array $plugins,
        public readonly LanguageCollection $languages,
        public readonly PluginMappingCollection $pluginMapping,
        public readonly Uri $metadataUrl,
        public readonly array $excludedLocales,
    ) {
    }

    public function getMappedPluginName(Plugin $plugin): string
    {
        $pluginName = $plugin->getName();

        return $this->pluginMapping->get($pluginName)->snippetName ?? $pluginName;
    }
}
