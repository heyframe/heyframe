<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Increment\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('framework')]
class IncrementGatewayNotFoundException extends HeyFrameHttpException
{
    public function __construct(string $pool)
    {
        parent::__construct(
            'Increment gateway for pool "{{ pool }}" was not found.',
            ['pool' => $pool]
        );
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__INCREMENT_GATEWAY_NOT_FOUND';
    }
}
