<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ImportExport\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('fundamentals@after-sales')]
class InvalidFileAccessTokenException extends HeyFrameHttpException
{
    public function __construct()
    {
        parent::__construct('Access to file denied due to invalid access token');
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }

    public function getErrorCode(): string
    {
        return 'CONTENT__IMPORT_EXPORT_FILE_INVALID_ACCESS_TOKEN';
    }
}
