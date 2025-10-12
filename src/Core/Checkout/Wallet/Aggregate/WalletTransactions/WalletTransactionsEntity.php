<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Wallet\Aggregate\WalletTransactions;

use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityExtraFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class WalletTransactionsEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityExtraFieldsTrait;
    use EntityIdTrait;

    protected string $txType;

    protected float $amount;

    protected float $balanceAfter;

    protected ?string $referencedType = null;

    protected ?string $referencedId = null;

    protected string $walletId;

    protected WalletEntity $wallet;

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

    public function getWalletId(): string
    {
        return $this->walletId;
    }

    public function setWalletId(string $walletId): void
    {
        $this->walletId = $walletId;
    }

    public function getWallet(): WalletEntity
    {
        return $this->wallet;
    }

    public function setWallet(WalletEntity $wallet): void
    {
        $this->wallet = $wallet;
    }
}
