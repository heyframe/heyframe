<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Uuid;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\HttpException;
use HeyFrame\Core\Framework\Uuid\Exception\InvalidUuidException;
use HeyFrame\Core\Framework\Uuid\Exception\InvalidUuidLengthException;

class UuidException extends HttpException
{
    public static function invalidUuid(string $uuid): HeyFrameHttpException
    {
        return new InvalidUuidException($uuid);
    }

    public static function invalidUuidLength(int $length, string $hex): HeyFrameHttpException
    {
        return new InvalidUuidLengthException($length, $hex);
    }
}
