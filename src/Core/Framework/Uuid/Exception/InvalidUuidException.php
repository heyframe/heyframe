<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Uuid\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use Symfony\Component\HttpFoundation\Response;

class InvalidUuidException extends HeyFrameHttpException
{
    public function __construct(string $uuid)
    {
        parent::__construct('Value is not a valid UUID: {{ input }}', ['input' => $uuid]);
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__INVALID_UUID';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }
}
