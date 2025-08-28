<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Event;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class EntityDeletedEvent extends EntityWrittenEvent
{
    public function __construct(
        string $entityName,
        array $writeResult,
        Context $context,
        array $errors = []
    ) {
        parent::__construct($entityName, $writeResult, $context, $errors);

        $this->name = $entityName . '.deleted';
    }
}
