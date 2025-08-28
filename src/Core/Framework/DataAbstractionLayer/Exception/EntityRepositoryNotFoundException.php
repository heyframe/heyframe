<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('framework')]
class EntityRepositoryNotFoundException extends HeyFrameHttpException
{
    public function __construct(string $entity)
    {
        parent::__construct(
            'EntityRepository for entity "{{ entityName }}" does not exist.',
            ['entityName' => $entity]
        );
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__EntityRepository_NOT_FOUND';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }
}
