<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class RepositoryNotFoundException extends HeyFrameHttpException
{
    public function __construct(string $entity)
    {
        parent::__construct('Repository for entity "{{ entityName }}" does not exist.', ['entityName' => $entity]);
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__REPOSITORY_NOT_FOUND';
    }
}
