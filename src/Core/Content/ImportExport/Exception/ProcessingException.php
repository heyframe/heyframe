<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ImportExport\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;

#[Package('fundamentals@after-sales')]
class ProcessingException extends HeyFrameHttpException
{
    public function getErrorCode(): string
    {
        return 'CONTENT__IMPORT_EXPORT_PROCESSING_EXCEPTION';
    }
}
