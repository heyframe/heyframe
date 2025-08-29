<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Dict\Aggregate\DictItem;

use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Dict\Aggregate\DictItemTranslation\DictItemTranslationEntity;
use HeyFrame\Core\System\Dict\DictEntity;

#[Package('framework')]
class DictItemEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    protected string $dictId;

    protected bool $active;

    protected string $value;

    protected ?string $label = null;

    protected ?string $description = null;

    protected ?int $position = null;

    protected ?DictEntity $dict = null;

    protected ?DictItemTranslationEntity $translations = null;

    public function getDictId(): string
    {
        return $this->dictId;
    }

    public function setDictId(string $dictId): void
    {
        $this->dictId = $dictId;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): void
    {
        $this->position = $position;
    }

    public function getDict(): ?DictEntity
    {
        return $this->dict;
    }

    public function setDict(?DictEntity $dict): void
    {
        $this->dict = $dict;
    }

    public function getTranslations(): ?DictItemTranslationEntity
    {
        return $this->translations;
    }

    public function setTranslations(?DictItemTranslationEntity $translations): void
    {
        $this->translations = $translations;
    }
}
