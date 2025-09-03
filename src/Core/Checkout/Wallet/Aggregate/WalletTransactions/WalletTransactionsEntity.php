<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Wallet\Aggregate\WalletTransactions;

use HeyFrame\Core\Checkout\Wallet\WalletEntity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityExtraFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use HeyFrame\Core\Framework\Log\Package;

#[Package('checkout')]
class WalletTransactionsEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityExtraFieldsTrait;
    use EntityIdTrait;

    protected string $walletId;

    protected string $txType;

    protected float $amount;

    protected float $balanceAfter;

    protected ?string $referencedId = null;

    protected ?string $referenceType = null;

    protected ?WalletEntity $wallet = null;

    public function getWalletId(): string
    {
        return $this->walletId;
    }

    public function setWalletId(string $walletId): void
    {
        $this->walletId = $walletId;
    }

    public function getTxType(): string
    {
        return $this->txType;
    }

    public function setTxType(string $txType): void
    {
        $this->txType = $txType;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    public function getBalanceAfter(): float
    {
        return $this->balanceAfter;
    }

    public function setBalanceAfter(float $balanceAfter): void
    {
        $this->balanceAfter = $balanceAfter;
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

    public function getWallet(): ?WalletEntity
    {
        return $this->wallet;
    }

    public function setWallet(?WalletEntity $wallet): void
    {
        $this->wallet = $wallet;
    }
}
