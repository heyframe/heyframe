<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Navigation\Aggregate\NavigationTranslation;

use HeyFrame\Core\Content\Navigation\NavigationEntity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\TranslationEntity;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Language\LanguageEntity;

#[Package('discovery')]
class NavigationTranslationEntity extends TranslationEntity
{
    use EntityCustomFieldsTrait;

    protected string $navigationId;

    protected ?string $name = null;

    protected ?NavigationEntity $navigation = null;

    protected ?LanguageEntity $language = null;

    protected ?string $linkType = null;

    protected ?bool $linkNewTab = null;

    protected ?string $internalLink = null;

    protected ?string $externalLink = null;

    protected ?string $description = null;

    protected ?string $metaTitle = null;

    protected ?string $metaDescription = null;

    protected ?string $keywords = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getLinkType(): ?string
    {
        return $this->linkType;
    }

    public function setLinkType(?string $linkType): void
    {
        $this->linkType = $linkType;
    }

    public function getLinkNewTab(): ?bool
    {
        return $this->linkNewTab;
    }

    public function setLinkNewTab(?bool $linkNewTab): void
    {
        $this->linkNewTab = $linkNewTab;
    }

    public function getInternalLink(): ?string
    {
        return $this->internalLink;
    }

    public function setInternalLink(?string $internalLink): void
    {
        $this->internalLink = $internalLink;
    }

    public function getExternalLink(): ?string
    {
        return $this->externalLink;
    }

    public function setExternalLink(string $externalLink): void
    {
        $this->externalLink = $externalLink;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(?string $metaTitle): void
    {
        $this->metaTitle = $metaTitle;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): void
    {
        $this->metaDescription = $metaDescription;
    }

    public function getKeywords(): ?string
    {
        return $this->keywords;
    }

    public function setKeywords(?string $keywords): void
    {
        $this->keywords = $keywords;
    }

    public function getNavigationId(): string
    {
        return $this->navigationId;
    }

    public function setNavigationId(string $navigationId): void
    {
        $this->navigationId = $navigationId;
    }

    public function getNavigation(): ?NavigationEntity
    {
        return $this->navigation;
    }

    public function setNavigation(?NavigationEntity $navigation): void
    {
        $this->navigation = $navigation;
    }

    public function getLanguage(): ?LanguageEntity
    {
        return $this->language;
    }

    public function setLanguage(?LanguageEntity $language): void
    {
        $this->language = $language;
    }
}
