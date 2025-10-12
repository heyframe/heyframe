<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Wallet\Exception;

use HeyFrame\Core\Checkout\Wallet\WalletException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('checkout')]
class WalletNotFoundException extends WalletException
{
    public function __construct(string $referencedType, string $referencedId)
    {
        parent::__construct(
            Response::HTTP_UNAUTHORIZED,
            self::WALLET_NOT_FOUND,
            'No wallet found for "{{ type }}" with ID "{{ id }}".',
            ['type' => $referencedType, 'id' => $referencedId]
        );
    }
}
