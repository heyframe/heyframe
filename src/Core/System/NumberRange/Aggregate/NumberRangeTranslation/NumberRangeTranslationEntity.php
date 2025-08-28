<?php declare(strict_types=1);

namespace HeyFrame\Core\System\NumberRange\Aggregate\NumberRangeTranslation;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\TranslationEntity;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\NumberRange\NumberRangeEntity;

#[Package('framework')]
class NumberRangeTranslationEntity extends TranslationEntity
{
    use EntityCustomFieldsTrait;

    protected string $numberRangeId;

    protected ?string $name = null;

    protected ?string $description = null;

    protected ?NumberRangeEntity $numberRange = null;

    public function getNumberRangeId(): string
    {
        return $this->numberRangeId;
    }

    public function setNumberRangeId(string $numberRangeId): void
    {
        $this->numberRangeId = $numberRangeId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getNumberRange(): ?NumberRangeEntity
    {
        return $this->numberRange;
    }

    public function setNumberRange(?NumberRangeEntity $numberRange): void
    {
        $this->numberRange = $numberRange;
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
