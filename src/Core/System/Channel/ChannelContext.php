<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel;

use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupEntity;
use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Checkout\Payment\PaymentMethodEntity;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Api\Context\ChannelApiSource;
use HeyFrame\Core\Framework\Api\Context\ContextSource;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Pricing\CashRoundingConfig;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\StateAwareTrait;
use HeyFrame\Core\Framework\Struct\Struct;
use HeyFrame\Core\System\Channel\Context\LanguageInfo;
use HeyFrame\Core\System\Currency\CurrencyEntity;
use Symfony\Component\Lock\LockInterface;

#[Package('framework')]
class ChannelContext extends Struct
{
    use StateAwareTrait;

    /**
     * @var array<string, bool>
     */
    protected array $permissions = [];

    protected bool $permisionsLocked = false;

    protected ContextSource $source;

    /**
     * @internal
     */
    protected ?LockInterface $cartLock = null;

    /**
     * @param array<string, array<string>> $areaRuleIds
     *
     * @internal
     */
    public function __construct(
        protected Context $context,
        protected string $token,
        private ?string $domainId,
        protected ChannelEntity $channel,
        protected CurrencyEntity $currency,
        protected CustomerGroupEntity $currentCustomerGroup,
        protected PaymentMethodEntity $paymentMethod,
        protected ?CustomerEntity $customer,
        protected CashRoundingConfig $itemRounding,
        protected CashRoundingConfig $totalRounding,
        protected LanguageInfo $languageInfo,
        protected array $areaRuleIds = [],
    ) {
        $this->source = $this->context->getSource();
    }

    public function getCurrentCustomerGroup(): CustomerGroupEntity
    {
        return $this->currentCustomerGroup;
    }

    public function getCurrency(): CurrencyEntity
    {
        return $this->currency;
    }

    public function getChannel(): ChannelEntity
    {
        return $this->channel;
    }

    public function getCustomer(): ?CustomerEntity
    {
        return $this->customer;
    }

    public function getPaymentMethod(): PaymentMethodEntity
    {
        return $this->paymentMethod;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    /**
     * @return array<string>
     */
    public function getRuleIds(): array
    {
        return $this->context->getRuleIds();
    }

    /**
     * @param array<string> $ruleIds
     */
    public function setRuleIds(array $ruleIds): void
    {
        $this->context->setRuleIds($ruleIds);
    }

    /**
     * @return array<string, array<string>>
     *
     * @internal
     */
    public function getAreaRuleIds(): array
    {
        return $this->areaRuleIds;
    }

    /**
     * @param array<string> $areas
     *
     * @return array<string>
     *
     * @internal
     */
    public function getRuleIdsByAreas(array $areas): array
    {
        $ruleIds = [];

        foreach ($areas as $area) {
            if (empty($this->areaRuleIds[$area])) {
                continue;
            }

            $ruleIds = array_unique(array_merge($ruleIds, $this->areaRuleIds[$area]));
        }

        return array_values($ruleIds);
    }

    /**
     * @param array<string, array<string>> $areaRuleIds
     *
     * @internal
     */
    public function setAreaRuleIds(array $areaRuleIds): void
    {
        $this->areaRuleIds = $areaRuleIds;
    }

    public function lockRules(): void
    {
        $this->context->lockRules();
    }

    public function lockPermissions(): void
    {
        $this->permisionsLocked = true;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return array<string, bool>
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * @param array<string, bool> $permissions
     */
    public function setPermissions(array $permissions): void
    {
        if ($this->permisionsLocked) {
            throw ChannelException::contextPermissionsLocked();
        }

        $this->permissions = array_filter($permissions);
    }

    public function getApiAlias(): string
    {
        return 'channel_context';
    }

    public function hasPermission(string $permission): bool
    {
        return \array_key_exists($permission, $this->permissions) && $this->permissions[$permission];
    }

    public function getChannelId(): string
    {
        return $this->channel->getId();
    }

    public function addState(string ...$states): void
    {
        $this->context->addState(...$states);
    }

    public function removeState(string $state): void
    {
        $this->context->removeState($state);
    }

    public function hasState(string ...$states): bool
    {
        return $this->context->hasState(...$states);
    }

    /**
     * @return array<string>
     */
    public function getStates(): array
    {
        return $this->context->getStates();
    }

    public function state(\Closure $closure, string ...$states): mixed
    {
        return $this->context->state(fn () => $closure($this), ...$states);
    }

    public function getDomainId(): ?string
    {
        return $this->domainId;
    }

    public function setDomainId(?string $domainId): void
    {
        $this->domainId = $domainId;
    }

    /**
     * @return non-empty-list<string>
     */
    public function getLanguageIdChain(): array
    {
        return $this->context->getLanguageIdChain();
    }

    public function getLanguageId(): string
    {
        return $this->context->getLanguageId();
    }

    public function getVersionId(): string
    {
        return $this->context->getVersionId();
    }

    public function considerInheritance(): bool
    {
        return $this->context->considerInheritance();
    }

    public function getTotalRounding(): CashRoundingConfig
    {
        return $this->totalRounding;
    }

    public function setTotalRounding(CashRoundingConfig $totalRounding): void
    {
        $this->totalRounding = $totalRounding;
    }

    public function getItemRounding(): CashRoundingConfig
    {
        return $this->itemRounding;
    }

    public function setItemRounding(CashRoundingConfig $itemRounding): void
    {
        $this->itemRounding = $itemRounding;
    }

    public function getCurrencyId(): string
    {
        return $this->currency->getId();
    }

    public function ensureLoggedIn(bool $allowGuest = true): void
    {
        if ($this->customer === null) {
            throw ChannelException::customerNotLoggedIn();
        }
    }

    public function getCustomerId(): ?string
    {
        return $this->customer?->getId();
    }

    /**
     * @template TReturn of mixed
     *
     * @param callable(ChannelContext): TReturn $callback
     *
     * @return TReturn the return value of the provided callback function
     */
    public function live(callable $callback): mixed
    {
        $before = $this->context;

        $this->context = $this->context->createWithVersionId(Defaults::LIVE_VERSION);

        $result = $callback($this);

        $this->context = $before;

        return $result;
    }

    /**
     * Executed the callback function with the given permissions set in the ChannelContext. If the
     * permissions are locked, the callback is called with the original permissions of the ChannelContext.
     *
     * @template TReturn of mixed
     *
     * @param array<string, bool> $permissions
     * @param callable(ChannelContext): TReturn $callback
     *
     * @return TReturn the return value of the provided callback function
     */
    public function withPermissions(array $permissions, callable $callback): mixed
    {
        if ($this->permisionsLocked) {
            return $callback($this);
        }

        $originalPermissions = $this->getPermissions();
        $permissions = array_merge($originalPermissions, $permissions);

        $this->setPermissions($permissions);

        $result = $callback($this);

        $this->setPermissions($originalPermissions);

        return $result;
    }

    public function isAllowed(string $privilege): bool
    {
        if ($this->source instanceof ChannelApiSource) {
            return $this->source->isAllowed($privilege);
        }

        return true;
    }

    public function getCustomerGroupId(): string
    {
        return $this->currentCustomerGroup->getId();
    }

    public function getLanguageInfo(): LanguageInfo
    {
        return $this->languageInfo;
    }

    public function setLanguageInfo(LanguageInfo $languageInfo): void
    {
        $this->languageInfo = $languageInfo;
    }

    /**
     * @internal
     */
    public function getCartLock(): ?LockInterface
    {
        return $this->cartLock;
    }

    /**
     * @internal
     */
    public function setCartLock(?LockInterface $cartLock): void
    {
        $this->cartLock = $cartLock;
    }
}
