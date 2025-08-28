<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('framework')]
class InvalidPageQueryException extends HeyFrameHttpException
{
    public function __construct(mixed $page)
    {
        parent::__construct('The page parameter must be a positive integer. Given: {{ page }}', ['page' => $page]);
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__INVALID_PAGE_QUERY';
    }
}
