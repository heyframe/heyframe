<?php declare(strict_types=1);

namespace HeyFrame\Core\System\User;

use HeyFrame\Core\Framework\HttpException;
use Symfony\Component\HttpFoundation\Response;

class UserException extends HttpException
{
    final public const SALES_CHANNEL_NOT_FOUND = 'USER__SALES_CHANNEL_NOT_FOUND';

    public static function salesChannelNotFound(): HttpException
    {
        return new self(
            Response::HTTP_PRECONDITION_FAILED,
            self::SALES_CHANNEL_NOT_FOUND,
            'No sales channel found.',
        );
    }
}
