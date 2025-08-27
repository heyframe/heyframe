<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Indexing;

use HeyFrame\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;

abstract class PostUpdateIndexer extends EntityIndexer
{
    final public function update(EntityWrittenContainerEvent $event): ?EntityIndexingMessage
    {
        return null;
    }
}
