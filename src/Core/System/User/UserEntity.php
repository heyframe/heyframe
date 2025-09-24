<?php declare(strict_types=1);

namespace HeyFrame\Core\System\User;

use HeyFrame\Core\Checkout\Customer\CustomerCollection;
use HeyFrame\Core\Checkout\Order\OrderCollection;
use HeyFrame\Core\Content\ImportExport\Aggregate\ImportExportLog\ImportExportLogCollection;
use HeyFrame\Core\Content\Media\MediaCollection;
use HeyFrame\Core\Content\Media\MediaEntity;
use HeyFrame\Core\Framework\Api\Acl\Admin\Role\AclRoleCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Locale\LocaleEntity;
use HeyFrame\Core\System\StateMachine\Aggregation\StateMachineHistory\StateMachineHistoryCollection;
use HeyFrame\Core\System\User\Aggregate\UserAccessKey\UserAccessKeyCollection;
use HeyFrame\Core\System\User\Aggregate\UserConfig\UserConfigCollection;
use HeyFrame\Core\System\User\Aggregate\UserRecovery\UserRecoveryEntity;

#[Package('fundamentals@framework')]
class UserEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    protected string $localeId;

    protected ?string $avatarId = null;

    protected string $username;

    /**
     * @internal
     */
    protected string $password;

    protected string $name;

    protected ?string $phoneNumber = null;

    protected string $email;

    protected bool $active;

    protected bool $admin;

    protected ?AclRoleCollection $aclRoles = null;

    protected ?LocaleEntity $locale = null;

    protected ?MediaEntity $avatarMedia = null;

    protected ?MediaCollection $media = null;

    protected ?UserAccessKeyCollection $accessKeys = null;

    protected ?UserConfigCollection $configs = null;

    protected ?StateMachineHistoryCollection $stateMachineHistoryEntries = null;

    protected ?ImportExportLogCollection $importExportLogEntries = null;

    protected ?UserRecoveryEntity $recoveryUser = null;

    /**
     * @internal
     */
    protected ?string $storeToken = null;

    protected ?\DateTimeInterface $lastUpdatedPasswordAt = null;

    protected ?OrderCollection $createdOrders = null;

    protected ?OrderCollection $updatedOrders = null;

    protected ?CustomerCollection $createdCustomers = null;

    protected ?CustomerCollection $updatedCustomers = null;

    protected string $timeZone;

    public function getStateMachineHistoryEntries(): ?StateMachineHistoryCollection
    {
        return $this->stateMachineHistoryEntries;
    }

    public function setStateMachineHistoryEntries(StateMachineHistoryCollection $stateMachineHistoryEntries): void
    {
        $this->stateMachineHistoryEntries = $stateMachineHistoryEntries;
    }

    public function getImportExportLogEntries(): ?ImportExportLogCollection
    {
        return $this->importExportLogEntries;
    }

    public function setImportExportLogEntries(ImportExportLogCollection $importExportLogEntries): void
    {
        $this->importExportLogEntries = $importExportLogEntries;
    }

    public function getLocaleId(): string
    {
        return $this->localeId;
    }

    public function setLocaleId(string $localeId): void
    {
        $this->localeId = $localeId;
    }

    public function getAvatarId(): ?string
    {
        return $this->avatarId;
    }

    public function setAvatarId(string $avatarId): void
    {
        $this->avatarId = $avatarId;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @internal
     */
    public function getPassword(): string
    {
        $this->checkIfPropertyAccessIsAllowed('password');

        return $this->password;
    }

    /**
     * @internal
     */
    public function setPassword(#[\SensitiveParameter] string $password): void
    {
        $this->password = $password;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getLocale(): ?LocaleEntity
    {
        return $this->locale;
    }

    public function setLocale(LocaleEntity $locale): void
    {
        $this->locale = $locale;
    }

    public function getAvatarMedia(): ?MediaEntity
    {
        return $this->avatarMedia;
    }

    public function setAvatarMedia(MediaEntity $avatarMedia): void
    {
        $this->avatarMedia = $avatarMedia;
    }

    public function getMedia(): ?MediaCollection
    {
        return $this->media;
    }

    public function setMedia(MediaCollection $media): void
    {
        $this->media = $media;
    }

    public function getAccessKeys(): ?UserAccessKeyCollection
    {
        return $this->accessKeys;
    }

    public function setAccessKeys(UserAccessKeyCollection $accessKeys): void
    {
        $this->accessKeys = $accessKeys;
    }

    public function getConfigs(): ?UserConfigCollection
    {
        return $this->configs;
    }

    public function setConfigs(UserConfigCollection $configs): void
    {
        $this->configs = $configs;
    }

    public function getRecoveryUser(): ?UserRecoveryEntity
    {
        return $this->recoveryUser;
    }

    public function setRecoveryUser(UserRecoveryEntity $recoveryUser): void
    {
        $this->recoveryUser = $recoveryUser;
    }

    /**
     * @internal
     */
    public function getStoreToken(): ?string
    {
        $this->checkIfPropertyAccessIsAllowed('storeToken');

        return $this->storeToken;
    }

    /**
     * @internal
     */
    public function setStoreToken(#[\SensitiveParameter] ?string $storeToken): void
    {
        $this->storeToken = $storeToken;
    }

    public function isAdmin(): bool
    {
        return $this->admin;
    }

    public function setAdmin(bool $admin): void
    {
        $this->admin = $admin;
    }

    public function getAclRoles(): ?AclRoleCollection
    {
        return $this->aclRoles;
    }

    public function setAclRoles(AclRoleCollection $aclRoles): void
    {
        $this->aclRoles = $aclRoles;
    }

    public function getCreatedOrders(): ?OrderCollection
    {
        return $this->createdOrders;
    }

    public function setCreatedOrders(OrderCollection $createdOrders): void
    {
        $this->createdOrders = $createdOrders;
    }

    public function getUpdatedOrders(): ?OrderCollection
    {
        return $this->updatedOrders;
    }

    public function setUpdatedOrders(OrderCollection $updatedOrders): void
    {
        $this->updatedOrders = $updatedOrders;
    }

    public function getCreatedCustomers(): ?CustomerCollection
    {
        return $this->createdCustomers;
    }

    public function setCreatedCustomers(CustomerCollection $createdCustomers): void
    {
        $this->createdCustomers = $createdCustomers;
    }

    public function getUpdatedCustomers(): ?CustomerCollection
    {
        return $this->updatedCustomers;
    }

    public function setUpdatedCustomers(CustomerCollection $updatedCustomers): void
    {
        $this->updatedCustomers = $updatedCustomers;
    }

    public function getLastUpdatedPasswordAt(): ?\DateTimeInterface
    {
        return $this->lastUpdatedPasswordAt;
    }

    public function setLastUpdatedPasswordAt(\DateTimeInterface $lastUpdatedPasswordAt): void
    {
        $this->lastUpdatedPasswordAt = $lastUpdatedPasswordAt;
    }

    public function getTimeZone(): string
    {
        return $this->timeZone;
    }

    public function setTimeZone(string $timeZone): void
    {
        $this->timeZone = $timeZone;
    }
}
