<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Event;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use HeyFrame\Core\Framework\Event\GenericEvent;
use HeyFrame\Core\Framework\Event\NestedEvent;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @template TEntityCollection of EntityCollection
 */
#[Package('framework')]
class EntitySearchResultLoadedEvent extends NestedEvent implements GenericEvent
{
    protected string $name;

    /**
     * @param EntitySearchResult<TEntityCollection> $result
     */
    public function __construct(
        protected EntityDefinition $definition,
        protected EntitySearchResult $result
    ) {
        $this->name = $this->definition->getEntityName() . '.search.result.loaded';
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getContext(): Context
    {
        return $this->result->getContext();
    }

    /**
     * @return EntitySearchResult<TEntityCollection>
     */
    public function getResult(): EntitySearchResult
    {
        return $this->result;
    }
}
