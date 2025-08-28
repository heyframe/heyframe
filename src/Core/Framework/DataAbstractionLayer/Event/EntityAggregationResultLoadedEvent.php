<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Event;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResultCollection;
use HeyFrame\Core\Framework\Event\GenericEvent;
use HeyFrame\Core\Framework\Event\NestedEvent;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class EntityAggregationResultLoadedEvent extends NestedEvent implements GenericEvent
{
    protected string $name;

    public function __construct(
        protected EntityDefinition $definition,
        protected AggregationResultCollection $result,
        protected Context $context
    ) {
        $this->name = $this->definition->getEntityName() . '.aggregation.result.loaded';
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getResult(): AggregationResultCollection
    {
        return $this->result;
    }
}
