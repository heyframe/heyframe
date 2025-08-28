<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Event;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;

#[Package('fundamentals@after-sales')]
class BusinessEventCollectorEvent extends NestedEvent
{
    final public const NAME = 'collect.business-events';

    public function __construct(
        private readonly BusinessEventCollectorResponse $events,
        private readonly Context $context
    ) {
    }

    public function getCollection(): BusinessEventCollectorResponse
    {
        return $this->events;
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
