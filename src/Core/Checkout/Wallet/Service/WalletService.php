<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Wallet\Service;

use HeyFrame\Core\Checkout\Wallet\WalletCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;

class WalletService
{
    /**
     * @param EntityRepository<WalletCollection> $walletRepository
     */
    public function __construct(
        private readonly EntityRepository $walletRepository,
    ) {
    }
}
