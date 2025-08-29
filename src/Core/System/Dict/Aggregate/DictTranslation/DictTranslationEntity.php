<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Dict\Aggregate\DictTranslation;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\TranslationEntity;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class DictTranslationEntity extends TranslationEntity
{
    use EntityCustomFieldsTrait;

    protected string $dictId;

    protected ?string $label = null;

    protected ?string $description = null;

    public function getDictId(): string
    {
        return $this->dictId;
    }

    public function setDictId(string $dictId): void
    {
        $this->dictId = $dictId;
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
}
