<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Points\Aggregate\PointsTransactions;

use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityExtraFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Points\PointsEntity;

#[Package('checkout')]
class PointsTransactionsEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityExtraFieldsTrait;
    use EntityIdTrait;

    protected string $pointsId;
    protected string $txType;
    protected int $pointsAmount;
    protected int $pointsAfter;
    protected ?string $referencedId = null;
    protected ?string $referenceType = null;

    protected ?PointsEntity $points = null;

    public function getPointsAmount(): int
    {
        return $this->pointsAmount;
    }

    public function setPointsAmount(int $pointsAmount): void
    {
        $this->pointsAmount = $pointsAmount;
    }

    public function getPointsAfter(): int
    {
        return $this->pointsAfter;
    }

    public function setPointsAfter(int $pointsAfter): void
    {
        $this->pointsAfter = $pointsAfter;
    }


    public function getPointsId(): string
    {
        return $this->pointsId;
    }

    public function setPointsId(string $pointsId): void
    {
        $this->pointsId = $pointsId;
    }

    public function getTxType(): string
    {
        return $this->txType;
    }

    public function setTxType(string $txType): void
    {
        $this->txType = $txType;
    }

    public function getReferencedId(): ?string
    {
        return $this->referencedId;
    }

    public function setReferencedId(?string $referencedId): void
    {
        $this->referencedId = $referencedId;
    }

    public function getReferenceType(): ?string
    {
        return $this->referenceType;
    }

    public function setReferenceType(?string $referenceType): void
    {
        $this->referenceType = $referenceType;
    }

    public function getPoints(): ?PointsEntity
    {
        return $this->points;
    }

    public function setPoints(?PointsEntity $points): void
    {
        $this->points = $points;
    }
}
