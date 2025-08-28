<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart;

use HeyFrame\Core\Checkout\Cart\Exception\CustomerNotLoggedInException;
use HeyFrame\Core\Framework\HttpException;
use Symfony\Component\HttpFoundation\Response;

class CartException extends HttpException
{
    public const CUSTOMER_NOT_LOGGED_IN_CODE = 'CHECKOUT__CUSTOMER_NOT_LOGGED_IN';

    public static function customerNotLoggedIn(): self
    {
        return new CustomerNotLoggedInException(
            Response::HTTP_FORBIDDEN,
            self::CUSTOMER_NOT_LOGGED_IN_CODE,
            'Customer is not logged in.'
        );
    }
}
