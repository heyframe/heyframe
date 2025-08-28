<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ImportExport\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('fundamentals@after-sales')]
class MediaDownloadException extends HeyFrameHttpException
{
    public function __construct(?string $url)
    {
        parent::__construct('Cannot download media from url: {{ url }}', ['url' => $url ?? 'null']);
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }

    public function getErrorCode(): string
    {
        return 'CONTENT__IMPORT_EXPORT_MEDIA_DOWNLOAD_FAILED';
    }
}
