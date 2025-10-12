<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Points;

use HeyFrame\Core\Checkout\Customer\CustomerDefinition;
use HeyFrame\Core\Checkout\Points\Aggregate\PointsLog\PointsLogCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityExtraFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use HeyFrame\Core\Framework\Log\Package;

#[Package('discovery')]
class PointsEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityExtraFieldsTrait;
    use EntityIdTrait;

    protected string $customerId;

    protected int $balance;

    protected int $frozen;

    protected ?CustomerDefinition $customer = null;

    protected ?PointsLogCollection $logs = null;

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function setCustomerId(string $customerId): void
    {
        $this->customerId = $customerId;
    }

    public function getBalance(): int
    {
        return $this->balance;
    }

    public function setBalance(int $balance): void
    {
        $this->balance = $balance;
    }

    public function getFrozen(): int
    {
        return $this->frozen;
    }

    public function setFrozen(int $frozen): void
    {
        $this->frozen = $frozen;
    }

    public function getCustomer(): ?CustomerDefinition
    {
        return $this->customer;
    }

    public function setCustomer(?CustomerDefinition $customer): void
    {
        $this->customer = $customer;
    }

    public function getLogs(): ?PointsLogCollection
    {
        return $this->logs;
    }

    public function setLogs(PointsLogCollection $logs): void
    {
        $this->logs = $logs;
    }
}
