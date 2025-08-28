<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Api\ApiDefinition;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('framework')]
class ApiTypeNotFoundException extends HeyFrameHttpException
{
    public function __construct(string $type)
    {
        parent::__construct(
            'A api type "{{ type }}" was not found.',
            ['type' => $type]
        );
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__API_DEFINITION_TYPE_NOT_SUPPORTED';
    }
}
