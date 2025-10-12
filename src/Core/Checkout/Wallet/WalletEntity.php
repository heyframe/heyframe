<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Wallet;

use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Checkout\Wallet\Aggregate\WalletTransactions\WalletTransactionsCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityExtraFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Currency\CurrencyEntity;

#[Package('checkout')]
class WalletEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityExtraFieldsTrait;
    use EntityIdTrait;

    protected string $customerId;

    protected float $balance;

    protected float $frozenBalance;

    protected float $bonusBalance;

    protected bool $active;

    protected string $currencyId;

    protected ?CurrencyEntity $currency = null;

    protected ?WalletTransactionsCollection $transactions = null;

    protected ?CustomerEntity $customer = null;

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function setCustomerId(string $customerId): void
    {
        $this->customerId = $customerId;
    }

    public function getCustomer(): ?CustomerEntity
    {
        return $this->customer;
    }

    public function setCustomer(?CustomerEntity $customer): void
    {
        $this->customer = $customer;
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function setBalance(float $balance): void
    {
        $this->balance = $balance;
    }

    public function getFrozenBalance(): float
    {
        return $this->frozenBalance;
    }

    public function setFrozenBalance(float $frozenBalance): void
    {
        $this->frozenBalance = $frozenBalance;
    }

    public function getBonusBalance(): float
    {
        return $this->bonusBalance;
    }

    public function setBonusBalance(float $bonusBalance): void
    {
        $this->bonusBalance = $bonusBalance;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getCurrencyId(): string
    {
        return $this->currencyId;
    }

    public function setCurrencyId(string $currencyId): void
    {
        $this->currencyId = $currencyId;
    }

    public function getCurrency(): ?CurrencyEntity
    {
        return $this->currency;
    }

    public function setCurrency(?CurrencyEntity $currency): void
    {
        $this->currency = $currency;
    }

    public function getTransactions(): ?WalletTransactionsCollection
    {
        return $this->transactions;
    }

    public function setTransactions(WalletTransactionsCollection $transactions): void
    {
        $this->transactions = $transactions;
    }
}
