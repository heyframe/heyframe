<?php

declare(strict_types=1);

namespace HeyFrame\Core\System\Snippet\Extension;

use HeyFrame\Core\Framework\Extensions\Extension;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\Translation\MessageCatalogueInterface;

/**
 * @extends Extension<array<string, string>>
 */
#[Package('discovery')]
final class FrontendSnippetsExtension extends Extension
{
    public const NAME = 'frontend.snippets';

    /**
     * @internal heyframe owns the __constructor, but the properties are public API
     *
     * @param array<string, string> $snippets
     * @param string[] $unusedThemes
     */
    public function __construct(
        public array $snippets,
        public readonly string $locale,
        public readonly MessageCatalogueInterface $catalog,
        public readonly string $snippetSetId,
        public readonly ?string $fallbackLocale,
        public readonly ?string $channelId,
        public readonly array $unusedThemes
    ) {
    }
}
