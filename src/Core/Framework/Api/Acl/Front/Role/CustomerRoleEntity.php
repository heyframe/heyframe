<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Api\Acl\Front\Role;

use HeyFrame\Core\Checkout\Customer\CustomerCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityExtraFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class CustomerRoleEntity extends Entity
{
    use EntityExtraFieldsTrait;
    use EntityIdTrait;

    protected string $name;

    /**
     * @var array<string, mixed>|null
     */
    protected ?array $config = null;

    protected bool $active;

    /**
     * @var list<string>
     */
    protected array $privileges = [];

    protected ?CustomerCollection $customers = null;

    protected ?\DateTimeInterface $deletedAt = null;

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getConfig(): ?array
    {
        return $this->config;
    }

    /**
     * @param array<string, mixed>|null $config
     */
    public function setConfig(?array $config): void
    {
        $this->config = $config;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return list<string>
     */
    public function getPrivileges(): array
    {
        return $this->privileges;
    }

    /**
     * @param list<string> $privileges
     */
    public function setPrivileges(array $privileges): void
    {
        $this->privileges = $privileges;
    }

    public function getCustomers(): ?CustomerCollection
    {
        return $this->customers;
    }

    public function setCustomers(CustomerCollection $customers): void
    {
        $this->customers = $customers;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(\DateTimeInterface $deletedAt): void
    {
        $this->deletedAt = $deletedAt;
    }
}
