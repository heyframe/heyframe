<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Exception;

use HeyFrame\Core\Checkout\Customer\CustomerException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('checkout')]
class CustomerRecoveryHashExpiredException extends CustomerException
{
    public function __construct(string $hash)
    {
        parent::__construct(
            Response::HTTP_GONE,
            self::CUSTOMER_RECOVERY_HASH_EXPIRED,
            'The hash "{{ hash }}" is expired.',
            ['hash' => $hash]
        );
    }
}
