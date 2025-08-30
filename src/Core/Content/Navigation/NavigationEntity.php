<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Navigation;

use HeyFrame\Core\Content\Media\MediaEntity;
use HeyFrame\Core\Content\Navigation\Aggregate\NavigationTranslation\NavigationTranslationCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use HeyFrame\Core\Framework\Log\Package;

#[Package('discovery')]
class NavigationEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    protected ?string $afterNavigationId = null;

    protected ?string $parentId = null;

    protected int $autoIncrement;

    protected ?string $mediaId = null;

    protected ?string $name = null;

    protected ?string $path = null;

    protected int $level;

    protected bool $active;

    protected int $childCount;

    protected int $visibleChildCount = 0;

    protected ?NavigationEntity $parent = null;

    protected ?NavigationCollection $children = null;

    protected ?NavigationTranslationCollection $translations = null;

    protected ?MediaEntity $media = null;

    protected ?string $linkType = null;

    protected ?bool $linkNewTab = null;

    protected ?string $internalLink = null;

    protected ?string $externalLink = null;

    protected bool $visible;

    protected string $type;

    public function getAfterNavigationId(): ?string
    {
        return $this->afterNavigationId;
    }

    public function setAfterNavigationId(?string $afterNavigationId): void
    {
        $this->afterNavigationId = $afterNavigationId;
    }

    public function getParentId(): ?string
    {
        return $this->parentId;
    }

    public function setParentId(?string $parentId): void
    {
        $this->parentId = $parentId;
    }

    public function getAutoIncrement(): int
    {
        return $this->autoIncrement;
    }

    public function setAutoIncrement(int $autoIncrement): void
    {
        $this->autoIncrement = $autoIncrement;
    }

    public function getMediaId(): ?string
    {
        return $this->mediaId;
    }

    public function setMediaId(?string $mediaId): void
    {
        $this->mediaId = $mediaId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): void
    {
        $this->path = $path;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getChildCount(): int
    {
        return $this->childCount;
    }

    public function setChildCount(int $childCount): void
    {
        $this->childCount = $childCount;
    }

    public function getVisibleChildCount(): int
    {
        return $this->visibleChildCount;
    }

    public function setVisibleChildCount(int $visibleChildCount): void
    {
        $this->visibleChildCount = $visibleChildCount;
    }

    public function getParent(): ?NavigationEntity
    {
        return $this->parent;
    }

    public function setParent(NavigationEntity $parent): void
    {
        $this->parent = $parent;
    }

    public function getChildren(): ?NavigationCollection
    {
        return $this->children;
    }

    public function setChildren(?NavigationCollection $children): void
    {
        $this->children = $children;
    }

    public function getTranslations(): ?NavigationTranslationCollection
    {
        return $this->translations;
    }

    public function setTranslations(NavigationTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }

    public function getMedia(): ?MediaEntity
    {
        return $this->media;
    }

    public function setMedia(?MediaEntity $media): void
    {
        $this->media = $media;
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

    public function setExternalLink(?string $externalLink): void
    {
        $this->externalLink = $externalLink;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): void
    {
        $this->visible = $visible;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }
}
