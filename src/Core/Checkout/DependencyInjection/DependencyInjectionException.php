<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\DependencyInjection;

use HeyFrame\Core\Framework\HttpException;
use HeyFrame\Core\Framework\Log\Package;

#[Package('checkout')]
class DependencyInjectionException extends HttpException
{
    public const CART_REDIS_NOT_CONFIGURED = 'CHECKOUT__CART_REDIS_NOT_CONFIGURED';

    public static function redisNotConfiguredForCartStorage(): self
    {
        return new self(
            500,
            self::CART_REDIS_NOT_CONFIGURED,
            'Parameter "heyframe.cart.storage.config.connection" is required for redis storage'
        );
    }
}
