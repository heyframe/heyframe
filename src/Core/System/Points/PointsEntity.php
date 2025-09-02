<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Points;

use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityExtraFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use HeyFrame\Core\Framework\Log\Package;

#[Package('checkout')]
class PointsEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityExtraFieldsTrait;
    use EntityIdTrait;

    protected int $totalPoints;

    protected int $availablePoints;

    protected int $frozenPoints;

    protected bool $active;

    protected string $identifier;

    protected string $referencedId;

    public function getTotalPoints(): int
    {
        return $this->totalPoints;
    }

    public function setTotalPoints(int $totalPoints): void
    {
        $this->totalPoints = $totalPoints;
    }

    public function getAvailablePoints(): int
    {
        return $this->availablePoints;
    }

    public function setAvailablePoints(int $availablePoints): void
    {
        $this->availablePoints = $availablePoints;
    }

    public function getFrozenPoints(): int
    {
        return $this->frozenPoints;
    }

    public function setFrozenPoints(int $frozenPoints): void
    {
        $this->frozenPoints = $frozenPoints;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function getReferencedId(): string
    {
        return $this->referencedId;
    }

    public function setReferencedId(string $referencedId): void
    {
        $this->referencedId = $referencedId;
    }
}
