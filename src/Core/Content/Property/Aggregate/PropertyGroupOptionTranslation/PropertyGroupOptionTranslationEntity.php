<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Property\Aggregate\PropertyGroupOptionTranslation;

use HeyFrame\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\TranslationEntity;
use HeyFrame\Core\Framework\Log\Package;

#[Package('inventory')]
class PropertyGroupOptionTranslationEntity extends TranslationEntity
{
    use EntityCustomFieldsTrait;

    protected string $propertyGroupOptionId;

    protected ?string $name = null;

    protected ?int $position = null;

    protected ?PropertyGroupOptionEntity $propertyGroupOption = null;

    public function getPropertyGroupOptionId(): string
    {
        return $this->propertyGroupOptionId;
    }

    public function setPropertyGroupOptionId(string $propertyGroupOptionId): void
    {
        $this->propertyGroupOptionId = $propertyGroupOptionId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getPropertyGroupOption(): ?PropertyGroupOptionEntity
    {
        return $this->propertyGroupOption;
    }

    public function setPropertyGroupOption(PropertyGroupOptionEntity $propertyGroupOption): void
    {
        $this->propertyGroupOption = $propertyGroupOption;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }
}
