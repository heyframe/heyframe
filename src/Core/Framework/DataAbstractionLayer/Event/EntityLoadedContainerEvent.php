<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Event;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\NestedEvent;
use HeyFrame\Core\Framework\Event\NestedEventCollection;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class EntityLoadedContainerEvent extends NestedEvent
{
    public function __construct(
        private readonly Context $context,
        private readonly array $events
    ) {
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getEvents(): ?NestedEventCollection
    {
        return new NestedEventCollection($this->events);
    }
}
