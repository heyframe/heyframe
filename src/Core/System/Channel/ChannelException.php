<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel;

use HeyFrame\Core\Checkout\Cart\CartException;
use HeyFrame\Core\Framework\HttpException;
use Symfony\Component\HttpFoundation\Response;

class ChannelException extends HttpException
{
    final public const CHANNEL_CONTEXT_PERMISSIONS_LOCKED = 'SYSTEM__CHANNEL_CONTEXT_PERMISSIONS_LOCKED';

    public static function customerNotLoggedIn(): CartException
    {
        return CartException::customerNotLoggedIn();
    }

    public static function contextPermissionsLocked(): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::CHANNEL_CONTEXT_PERMISSIONS_LOCKED,
            'Context permission in Channel context already locked.'
        );
    }
}
