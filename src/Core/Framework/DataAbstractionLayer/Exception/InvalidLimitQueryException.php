<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use Symfony\Component\HttpFoundation\Response;

class InvalidLimitQueryException extends HeyFrameHttpException
{
    public function __construct(mixed $limit)
    {
        parent::__construct(
            'The limit parameter must be a positive integer greater or equals than 1. Given: {{ limit }}',
            ['limit' => $limit]
        );
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__INVALID_LIMIT_QUERY';
    }
}
