<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Api\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class NoEntityClonedException extends HeyFrameHttpException
{
    public function __construct(
        string $entity,
        string $id
    ) {
        parent::__construct(
            'Could not clone entity {{ entity }} with id {{ id }}.',
            ['entity' => $entity, 'id' => $id]
        );
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__NO_ENTITIY_CLONED_ERROR';
    }
}
