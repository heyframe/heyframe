<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Language;

/**
 * @phpstan-type LanguageData array<string, array{id: string, code: string, parentId: string, parentCode?: ?string}>
 */
interface LanguageLoaderInterface
{
    /**
     * @return LanguageData
     */
    public function loadLanguages(): array;
}
