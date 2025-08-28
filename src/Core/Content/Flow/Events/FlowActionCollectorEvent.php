<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Flow\Events;

use HeyFrame\Core\Content\Flow\Api\FlowActionCollectorResponse;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\NestedEvent;
use HeyFrame\Core\Framework\Log\Package;

#[Package('after-sales')]
class FlowActionCollectorEvent extends NestedEvent
{
    public function __construct(
        private readonly FlowActionCollectorResponse $flowActionCollectorResponse,
        private readonly Context $context
    ) {
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getCollection(): FlowActionCollectorResponse
    {
        return $this->flowActionCollectorResponse;
    }
}
