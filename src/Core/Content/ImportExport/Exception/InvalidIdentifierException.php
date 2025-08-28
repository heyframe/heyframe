<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ImportExport\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('fundamentals@after-sales')]
class InvalidIdentifierException extends HeyFrameHttpException
{
    public function __construct(string $fieldName)
    {
        parent::__construct('The identifier of {{ fieldName }} should not contain pipe character.', ['fieldName' => $fieldName]);
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }

    public function getErrorCode(): string
    {
        return 'CONTENT__IMPORT_EXPORT_INVALID_IDENTIFIER';
    }
}
