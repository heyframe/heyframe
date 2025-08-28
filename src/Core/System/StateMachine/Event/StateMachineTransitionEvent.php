<?php declare(strict_types=1);

namespace HeyFrame\Core\System\StateMachine\Event;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\NestedEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateEntity;

#[Package('checkout')]
class StateMachineTransitionEvent extends NestedEvent
{
    public function __construct(
        protected string $entityName,
        protected string $entityId,
        protected StateMachineStateEntity $fromPlace,
        protected StateMachineStateEntity $toPlace,
        protected Context $context,
    ) {
    }

    public function getEntityName(): string
    {
        return $this->entityName;
    }

    public function getEntityId(): string
    {
        return $this->entityId;
    }

    public function getFromPlace(): StateMachineStateEntity
    {
        return $this->fromPlace;
    }

    public function getToPlace(): StateMachineStateEntity
    {
        return $this->toPlace;
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
