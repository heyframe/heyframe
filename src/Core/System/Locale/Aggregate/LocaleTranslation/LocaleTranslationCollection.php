<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Locale\Aggregate\LocaleTranslation;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<LocaleTranslationEntity>
 */
class LocaleTranslationCollection extends EntityCollection
{
    public function getLocaleIds(): array
    {
        return $this->fmap(fn (LocaleTranslationEntity $localeTranslation) => $localeTranslation->getLocaleId());
    }

    public function filterByLocaleId(string $id): self
    {
        return $this->filter(fn (LocaleTranslationEntity $localeTranslation) => $localeTranslation->getLocaleId() === $id);
    }

    public function getLanguageIds(): array
    {
        return $this->fmap(fn (LocaleTranslationEntity $localeTranslation) => $localeTranslation->getLanguageId());
    }

    public function filterByLanguageId(string $id): self
    {
        return $this->filter(fn (LocaleTranslationEntity $localeTranslation) => $localeTranslation->getLanguageId() === $id);
    }

    public function getApiAlias(): string
    {
        return 'locale_translation_collection';
    }

    protected function getExpectedClass(): string
    {
        return LocaleTranslationEntity::class;
    }
}
