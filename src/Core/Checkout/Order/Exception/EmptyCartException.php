<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Order\Exception;

use HeyFrame\Core\Checkout\Cart\CartException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('checkout')]
class EmptyCartException extends CartException
{
    public function __construct()
    {
        parent::__construct(
            Response::HTTP_BAD_REQUEST,
            self::CART_EMPTY,
            'Cart is empty.',
        );
    }
}
