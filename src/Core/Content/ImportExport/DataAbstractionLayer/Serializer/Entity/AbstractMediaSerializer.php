<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ImportExport\DataAbstractionLayer\Serializer\Entity;

use HeyFrame\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use HeyFrame\Core\Framework\Log\Package;

#[Package('fundamentals@after-sales')]
abstract class AbstractMediaSerializer extends EntitySerializer
{
    abstract public function persistMedia(EntityWrittenEvent $event): void;
}
