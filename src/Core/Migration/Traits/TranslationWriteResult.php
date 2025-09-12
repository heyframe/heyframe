<?php declare(strict_types=1);

namespace HeyFrame\Core\Migration\Traits;

use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class TranslationWriteResult
{
    /**
     * @param string[] $englishLanguages
     * @param string[] $chineseLanguages
     */
    public function __construct(
        private readonly array $englishLanguages,
        private readonly array $chineseLanguages
    ) {
    }

    /**
     * @return array<string>
     */
    public function getEnglishLanguages(): array
    {
        return $this->englishLanguages;
    }

    /**
     * @return array<string>
     */
    public function getChineseLanguages(): array
    {
        return $this->chineseLanguages;
    }

    public function hasWrittenEnglishTranslations(): bool
    {
        return \count($this->englishLanguages) > 0;
    }

    public function hasWrittenGermanTranslations(): bool
    {
        return \count($this->getChineseLanguages()) > 0;
    }
}
