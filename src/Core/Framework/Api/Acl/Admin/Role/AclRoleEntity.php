<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Api\Acl\Admin\Role;

use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Integration\IntegrationCollection;
use HeyFrame\Core\System\User\UserCollection;

#[Package('framework')]
class AclRoleEntity extends Entity
{
    use EntityIdTrait;

    protected string $name;

    protected ?string $description = null;

    /**
     * @var list<string>
     */
    protected array $privileges = [];

    protected ?UserCollection $users = null;

    protected ?IntegrationCollection $integrations = null;

    protected ?\DateTimeInterface $deletedAt = null;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getUsers(): ?UserCollection
    {
        return $this->users;
    }

    public function setUsers(UserCollection $users): void
    {
        $this->users = $users;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getIntegrations(): ?IntegrationCollection
    {
        return $this->integrations;
    }

    public function setIntegrations(IntegrationCollection $integrations): void
    {
        $this->integrations = $integrations;
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
