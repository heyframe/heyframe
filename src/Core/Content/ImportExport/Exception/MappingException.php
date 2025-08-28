<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ImportExport\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('fundamentals@after-sales')]
class MappingException extends HeyFrameHttpException
{
    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }

    public function getErrorCode(): string
    {
        return 'CONTENT__IMPORT_EXPORT_MAPPING_EXCEPTION';
    }
}
