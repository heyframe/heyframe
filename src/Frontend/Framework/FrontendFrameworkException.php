<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Framework;

use HeyFrame\Core\Framework\HttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

/**
 * @codeCoverageIgnore
 */
#[Package('framework')]
class FrontendFrameworkException extends HttpException
{
    public const APP_REQUEST_NOT_AVAILABLE = 'STOREFRONT__APP_REQUEST_NOT_AVAILABLE';
    public const CHANNEL_CONTEXT_OBJECT_NOT_FOUND = 'STOREFRONT__CHANNEL_CONTEXT_OBJECT_NOT_FOUND';
    public const MEDIA_ILLEGAL_FILE_TYPE = 'STOREFRONT__MEDIA_ILLEGAL_FILE_TYPE';

    public const INVALID_ARGUMENT = 'STOREFRONT__INVALID_ARGUMENT';

    public static function channelContextObjectNotFound(): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::CHANNEL_CONTEXT_OBJECT_NOT_FOUND,
            'Missing channel context object',
        );
    }
}
