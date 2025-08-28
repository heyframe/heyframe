<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Language\LanguageEntity;

#[Package('framework')]
class TranslationEntity extends Entity
{
    protected string $languageId;

    protected ?LanguageEntity $language = null;

    public function getLanguageId(): string
    {
        return $this->languageId;
    }

    public function setLanguageId(string $languageId): void
    {
        $this->languageId = $languageId;
    }

    public function getLanguage(): ?LanguageEntity
    {
        return $this->language;
    }

    public function setLanguage(LanguageEntity $language): void
    {
        $this->language = $language;
    }
}
