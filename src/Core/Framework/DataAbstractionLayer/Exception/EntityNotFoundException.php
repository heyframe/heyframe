<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('framework')]
class EntityNotFoundException extends HeyFrameHttpException
{
    public function __construct(
        string $entity,
        string $identifier
    ) {
        parent::__construct(
            '{{ entity }} for id {{ identifier }} not found.',
            ['entity' => $entity, 'identifier' => $identifier]
        );
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__ENTITY_NOT_FOUND';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }
}
