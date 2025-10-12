<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Points\Aggregate\PointsLog;

use HeyFrame\Core\Checkout\Points\PointsEntity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityExtraFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use HeyFrame\Core\Framework\Log\Package;

#[Package('discovery')]
class PointsLogEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityExtraFieldsTrait;
    use EntityIdTrait;

    protected string $pointsId;

    protected string $type;

    protected int $amount;

    protected int $balanceAfter;

    protected ?string $referencedType = null;

    protected ?string $referencedId = null;

    protected ?PointsEntity $pointsEntity = null;

    public function getPointsId(): string
    {
        return $this->pointsId;
    }

    public function setPointsId(string $pointsId): void
    {
        $this->pointsId = $pointsId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    public function getBalanceAfter(): int
    {
        return $this->balanceAfter;
    }

    public function setBalanceAfter(int $balanceAfter): void
    {
        $this->balanceAfter = $balanceAfter;
    }

    public function getReferencedType(): ?string
    {
        return $this->referencedType;
    }

    public function setReferencedType(?string $referencedType): void
    {
        $this->referencedType = $referencedType;
    }

    public function getReferencedId(): ?string
    {
        return $this->referencedId;
    }

    public function setReferencedId(?string $referencedId): void
    {
        $this->referencedId = $referencedId;
    }

    public function getPointsEntity(): ?PointsEntity
    {
        return $this->pointsEntity;
    }

    public function setPointsEntity(?PointsEntity $pointsEntity): void
    {
        $this->pointsEntity = $pointsEntity;
    }
}
