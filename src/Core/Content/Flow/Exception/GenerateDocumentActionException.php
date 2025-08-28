<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Flow\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;

class GenerateDocumentActionException extends HeyFrameHttpException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }

    public function getErrorCode(): string
    {
        return 'FLOW_BUILDER__DOCUMENT_GENERATION_ERROR';
    }
}
