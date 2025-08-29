<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Dict\Aggregate\DictItemTranslation;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\TranslationEntity;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class DictItemTranslationEntity extends TranslationEntity
{
    use EntityCustomFieldsTrait;

    protected string $dictItemId;

    protected ?string $label = null;

    protected ?string $description = null;

    protected ?int $position = null;

    public function getDictItemId(): string
    {
        return $this->dictItemId;
    }

    public function setDictItemId(string $dictItemId): void
    {
        $this->dictItemId = $dictItemId;
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
}
