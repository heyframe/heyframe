<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Exception;

use HeyFrame\Core\Checkout\Customer\CustomerException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('checkout')]
class BadCredentialsException extends CustomerException
{
    public function __construct()
    {
        parent::__construct(
            Response::HTTP_UNAUTHORIZED,
            self::CUSTOMER_AUTH_BAD_CREDENTIALS,
            'Invalid username and/or password.'
        );
    }
}
