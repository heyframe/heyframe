<?php declare(strict_types=1);

namespace HeyFrame\Core\System\User;

use HeyFrame\Core\Framework\HttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('fundamentals@framework')]
class UserException extends HttpException
{
    final public const CHANNEL_NOT_FOUND = 'USER__CHANNEL_NOT_FOUND';

    public static function channelNotFound(): HttpException
    {
        return new self(
            Response::HTTP_PRECONDITION_FAILED,
            self::CHANNEL_NOT_FOUND,
            'No sales channel found.',
        );
    }
}
