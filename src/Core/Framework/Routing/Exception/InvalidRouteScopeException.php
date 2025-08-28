<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Routing\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('framework')]
class InvalidRouteScopeException extends HeyFrameHttpException
{
    public function __construct(?string $routeName)
    {
        parent::__construct(
            'Invalid route scope for route {{ routeName }}.',
            ['routeName' => $routeName ?? '']
        );
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__ROUTING_INVALID_ROUTE_SCOPE';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_PRECONDITION_FAILED;
    }
}
