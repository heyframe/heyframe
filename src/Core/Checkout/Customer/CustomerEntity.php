<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer;

use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupEntity;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderCustomer\OrderCustomerCollection;
use HeyFrame\Core\Checkout\Payment\PaymentMethodEntity;
use HeyFrame\Core\Checkout\Promotion\PromotionCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelEntity;
use HeyFrame\Core\System\Language\LanguageEntity;
use HeyFrame\Core\System\User\UserEntity;

#[Package('checkout')]
class CustomerEntity extends Entity implements \Stringable
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    protected string $groupId;

    protected string $channelId;

    protected string $languageId;

    protected ?string $lastPaymentMethodId = null;

    protected string $defaultBillingAddressId;

    protected string $defaultShippingAddressId;

    protected string $customerNumber;

    protected ?string $salutationId = null;

    protected string $firstName;

    protected string $lastName;

    protected ?string $company = null;

    /**
     * @internal
     */
    protected ?string $password = null;

    protected string $email;

    protected ?string $title = null;

    /**
     * @var array<string>|null
     */
    protected ?array $vatIds = null;

    protected ?string $affiliateCode = null;

    protected ?string $campaignCode = null;

    protected bool $active;

    protected bool $doubleOptInRegistration;

    protected ?\DateTimeInterface $doubleOptInEmailSentDate = null;

    protected ?\DateTimeInterface $doubleOptInConfirmDate = null;

    protected ?string $hash = null;

    protected bool $guest;

    protected ?\DateTimeInterface $firstLogin = null;

    protected ?\DateTimeInterface $lastLogin = null;

    protected string $accountType;

    /**
     * @var array<string>|null
     *
     * @internal
     */
    protected ?array $newsletterChannelIds = null;

    protected ?\DateTimeInterface $birthday = null;

    protected ?\DateTimeInterface $lastOrderDate = null;

    protected int $orderCount;

    protected float $orderTotalAmount;

    protected int $reviewCount;

    /**
     * @internal
     */
    protected ?string $legacyEncoder = null;

    /**
     * @internal
     */
    protected ?string $legacyPassword = null;

    protected ?CustomerGroupEntity $group = null;

    protected ?ChannelEntity $channel = null;

    protected ?LanguageEntity $language = null;

    protected ?PaymentMethodEntity $lastPaymentMethod = null;

    protected ?OrderCustomerCollection $orderCustomers = null;

    protected int $autoIncrement;

    /**
     * @var list<string>|null
     */
    protected ?array $tagIds = null;

    protected ?PromotionCollection $promotions = null;

    protected ?string $remoteAddress = null;

    protected ?string $requestedGroupId = null;

    protected ?CustomerGroupEntity $requestedGroup = null;

    protected ?string $boundChannelId = null;

    protected ?ChannelEntity $boundChannel = null;

    protected ?string $createdById = null;

    protected ?UserEntity $createdBy = null;

    protected ?string $updatedById = null;

    protected ?UserEntity $updatedBy = null;

    public function __toString(): string
    {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    public function getGroupId(): string
    {
        return $this->groupId;
    }

    public function setGroupId(string $groupId): void
    {
        $this->groupId = $groupId;
    }

    public function getChannelId(): string
    {
        return $this->channelId;
    }

    public function setChannelId(string $channelId): void
    {
        $this->channelId = $channelId;
    }

    public function getLanguageId(): string
    {
        return $this->languageId;
    }

    public function setLanguageId(string $languageId): void
    {
        $this->languageId = $languageId;
    }

    public function getLastPaymentMethodId(): ?string
    {
        return $this->lastPaymentMethodId;
    }

    public function setLastPaymentMethodId(?string $lastPaymentMethodId): void
    {
        $this->lastPaymentMethodId = $lastPaymentMethodId;
    }

    public function getDefaultBillingAddressId(): string
    {
        return $this->defaultBillingAddressId;
    }

    public function setDefaultBillingAddressId(string $defaultBillingAddressId): void
    {
        $this->defaultBillingAddressId = $defaultBillingAddressId;
    }

    public function getDefaultShippingAddressId(): string
    {
        return $this->defaultShippingAddressId;
    }

    public function setDefaultShippingAddressId(string $defaultShippingAddressId): void
    {
        $this->defaultShippingAddressId = $defaultShippingAddressId;
    }

    public function getCustomerNumber(): string
    {
        return $this->customerNumber;
    }

    public function setCustomerNumber(string $customerNumber): void
    {
        $this->customerNumber = $customerNumber;
    }

    public function getSalutationId(): ?string
    {
        return $this->salutationId;
    }

    public function setSalutationId(string $salutationId): void
    {
        $this->salutationId = $salutationId;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(string $company): void
    {
        $this->company = $company;
    }

    /**
     * @internal
     */
    public function getPassword(): ?string
    {
        $this->checkIfPropertyAccessIsAllowed('password');

        return $this->password;
    }

    /**
     * @internal
     */
    public function setPassword(#[\SensitiveParameter] ?string $password): void
    {
        $this->password = $password;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return array<string>|null
     */
    public function getVatIds(): ?array
    {
        return $this->vatIds;
    }

    /**
     * @param array<string>|null $vatIds
     */
    public function setVatIds(?array $vatIds): void
    {
        $this->vatIds = $vatIds;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getDoubleOptInRegistration(): bool
    {
        return $this->doubleOptInRegistration;
    }

    public function setDoubleOptInRegistration(bool $doubleOptInRegistration): void
    {
        $this->doubleOptInRegistration = $doubleOptInRegistration;
    }

    public function getDoubleOptInEmailSentDate(): ?\DateTimeInterface
    {
        return $this->doubleOptInEmailSentDate;
    }

    public function setDoubleOptInEmailSentDate(\DateTimeInterface $doubleOptInEmailSentDate): void
    {
        $this->doubleOptInEmailSentDate = $doubleOptInEmailSentDate;
    }

    public function getDoubleOptInConfirmDate(): ?\DateTimeInterface
    {
        return $this->doubleOptInConfirmDate;
    }

    public function setDoubleOptInConfirmDate(\DateTimeInterface $doubleOptInConfirmDate): void
    {
        $this->doubleOptInConfirmDate = $doubleOptInConfirmDate;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(string $hash): void
    {
        $this->hash = $hash;
    }

    public function getGuest(): bool
    {
        return $this->guest;
    }

    public function setGuest(bool $guest): void
    {
        $this->guest = $guest;
    }

    public function getFirstLogin(): ?\DateTimeInterface
    {
        return $this->firstLogin;
    }

    public function setFirstLogin(?\DateTimeInterface $firstLogin): void
    {
        $this->firstLogin = $firstLogin;
    }

    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?\DateTimeInterface $lastLogin): void
    {
        $this->lastLogin = $lastLogin;
    }

    /**
     * @internal
     *
     * @return array<string>|null
     */
    public function getNewsletterChannelIds(): ?array
    {
        $this->checkIfPropertyAccessIsAllowed('newsletterChannelIds');

        return $this->newsletterChannelIds;
    }

    /**
     * @internal
     *
     * @param array<string>|null $newsletterChannelIds
     */
    public function setNewsletterChannelIds(?array $newsletterChannelIds): void
    {
        $this->newsletterChannelIds = $newsletterChannelIds;
    }

    public function getBirthday(): ?\DateTimeInterface
    {
        return $this->birthday;
    }

    public function setBirthday(?\DateTimeInterface $birthday): void
    {
        $this->birthday = $birthday;
    }

    public function getLastOrderDate(): ?\DateTimeInterface
    {
        return $this->lastOrderDate;
    }

    public function setLastOrderDate(?\DateTimeInterface $lastOrderDate): void
    {
        $this->lastOrderDate = $lastOrderDate;
    }

    public function getOrderCount(): int
    {
        return $this->orderCount;
    }

    public function setOrderCount(int $orderCount): void
    {
        $this->orderCount = $orderCount;
    }

    public function getOrderTotalAmount(): float
    {
        return $this->orderTotalAmount;
    }

    public function getReviewCount(): int
    {
        return $this->reviewCount;
    }

    public function setReviewCount(int $reviewCount): void
    {
        $this->reviewCount = $reviewCount;
    }

    public function setOrderTotalAmount(float $orderTotalAmount): void
    {
        $this->orderTotalAmount = $orderTotalAmount;
    }

    /**
     * @internal
     */
    public function getLegacyEncoder(): ?string
    {
        $this->checkIfPropertyAccessIsAllowed('legacyEncoder');

        return $this->legacyEncoder;
    }

    /**
     * @internal
     */
    public function setLegacyEncoder(?string $legacyEncoder): void
    {
        $this->legacyEncoder = $legacyEncoder;
    }

    /**
     * @internal
     */
    public function getLegacyPassword(): ?string
    {
        $this->checkIfPropertyAccessIsAllowed('legacyPassword');

        return $this->legacyPassword;
    }

    /**
     * @internal
     */
    public function setLegacyPassword(#[\SensitiveParameter] ?string $legacyPassword): void
    {
        $this->legacyPassword = $legacyPassword;
    }

    public function hasLegacyPassword(): bool
    {
        return $this->legacyPassword !== null && $this->legacyEncoder !== null;
    }

    public function getGroup(): ?CustomerGroupEntity
    {
        return $this->group;
    }

    public function setGroup(CustomerGroupEntity $group): void
    {
        $this->group = $group;
    }

    public function getChannel(): ?ChannelEntity
    {
        return $this->channel;
    }

    public function setChannel(ChannelEntity $channel): void
    {
        $this->channel = $channel;
    }

    public function getLanguage(): ?LanguageEntity
    {
        return $this->language;
    }

    public function setLanguage(LanguageEntity $language): void
    {
        $this->language = $language;
    }

    public function getLastPaymentMethod(): ?PaymentMethodEntity
    {
        return $this->lastPaymentMethod;
    }

    public function setLastPaymentMethod(PaymentMethodEntity $lastPaymentMethod): void
    {
        $this->lastPaymentMethod = $lastPaymentMethod;
    }

    public function getOrderCustomers(): ?OrderCustomerCollection
    {
        return $this->orderCustomers;
    }

    public function setOrderCustomers(OrderCustomerCollection $orderCustomers): void
    {
        $this->orderCustomers = $orderCustomers;
    }

    public function getAutoIncrement(): int
    {
        return $this->autoIncrement;
    }

    public function setAutoIncrement(int $autoIncrement): void
    {
        $this->autoIncrement = $autoIncrement;
    }

    /**
     * @return list<string>|null
     */
    public function getTagIds(): ?array
    {
        return $this->tagIds;
    }

    /**
     * @param list<string> $tagIds
     */
    public function setTagIds(array $tagIds): void
    {
        $this->tagIds = $tagIds;
    }

    /**
     * Gets a list of all promotions where the customer
     * is assigned to within the "persona" conditions.
     */
    public function getPromotions(): ?PromotionCollection
    {
        return $this->promotions;
    }

    /**
     * Sets a list of all promotions where the customer
     * should be assigned to within the "persona" conditions.
     */
    public function setPromotions(PromotionCollection $promotions): void
    {
        $this->promotions = $promotions;
    }

    public function getAffiliateCode(): ?string
    {
        return $this->affiliateCode;
    }

    public function setAffiliateCode(?string $affiliateCode): void
    {
        $this->affiliateCode = $affiliateCode;
    }

    public function getCampaignCode(): ?string
    {
        return $this->campaignCode;
    }

    public function setCampaignCode(?string $campaignCode): void
    {
        $this->campaignCode = $campaignCode;
    }

    public function getRemoteAddress(): ?string
    {
        return $this->remoteAddress;
    }

    public function setRemoteAddress(?string $remoteAddress): void
    {
        $this->remoteAddress = $remoteAddress;
    }

    public function getRequestedGroupId(): ?string
    {
        return $this->requestedGroupId;
    }

    public function setRequestedGroupId(?string $requestedGroupId): void
    {
        $this->requestedGroupId = $requestedGroupId;
    }

    public function getRequestedGroup(): ?CustomerGroupEntity
    {
        return $this->requestedGroup;
    }

    public function setRequestedGroup(?CustomerGroupEntity $requestedGroup): void
    {
        $this->requestedGroup = $requestedGroup;
    }

    public function getBoundChannelId(): ?string
    {
        return $this->boundChannelId;
    }

    public function setBoundChannelId(?string $boundChannelId): void
    {
        $this->boundChannelId = $boundChannelId;
    }

    public function getBoundChannel(): ?ChannelEntity
    {
        return $this->boundChannel;
    }

    public function setBoundChannel(ChannelEntity $boundChannel): void
    {
        $this->boundChannel = $boundChannel;
    }

    public function setAccountType(string $accountType): void
    {
        $this->accountType = $accountType;
    }

    public function getAccountType(): string
    {
        return $this->accountType;
    }

    public function getCreatedById(): ?string
    {
        return $this->createdById;
    }

    public function setCreatedById(string $createdById): void
    {
        $this->createdById = $createdById;
    }

    public function getCreatedBy(): ?UserEntity
    {
        return $this->createdBy;
    }

    public function setCreatedBy(UserEntity $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    public function getUpdatedById(): ?string
    {
        return $this->updatedById;
    }

    public function setUpdatedById(string $updatedById): void
    {
        $this->updatedById = $updatedById;
    }

    public function getUpdatedBy(): ?UserEntity
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(UserEntity $updatedBy): void
    {
        $this->updatedBy = $updatedBy;
    }
}
