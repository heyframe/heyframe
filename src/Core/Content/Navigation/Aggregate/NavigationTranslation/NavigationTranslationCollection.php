<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Navigation\Aggregate\NavigationTranslation;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<NavigationTranslationEntity>
 */
#[Package('discovery')]
class NavigationTranslationCollection extends EntityCollection
{
    /**
     * @return array<string>
     */
    public function getNavigationIds(): array
    {
        return $this->fmap(fn (NavigationTranslationEntity $navigationTranslation) => $navigationTranslation->getNavigationId());
    }

    public function filterByNavigationId(string $id): self
    {
        return $this->filter(fn (NavigationTranslationEntity $navigationTranslation) => $navigationTranslation->getNavigationId() === $id);
    }

    /**
     * @return array<string>
     */
    public function getLanguageIds(): array
    {
        return $this->fmap(fn (NavigationTranslationEntity $navigationTranslation) => $navigationTranslation->getLanguageId());
    }

    public function filterByLanguageId(string $id): self
    {
        return $this->filter(fn (NavigationTranslationEntity $navigationTranslation) => $navigationTranslation->getLanguageId() === $id);
    }

    public function getApiAlias(): string
    {
        return 'navigation_translation_collection';
    }

    protected function getExpectedClass(): string
    {
        return NavigationTranslationEntity::class;
    }
}
