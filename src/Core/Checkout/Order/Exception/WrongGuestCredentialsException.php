<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Order\Exception;

use HeyFrame\Core\Checkout\Order\OrderException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('checkout')]
class WrongGuestCredentialsException extends OrderException
{
    public function __construct()
    {
        parent::__construct(
            Response::HTTP_FORBIDDEN,
            parent::CHECKOUT_GUEST_WRONG_CREDENTIALS,
            'Wrong credentials for guest authentication.'
        );
    }
}
