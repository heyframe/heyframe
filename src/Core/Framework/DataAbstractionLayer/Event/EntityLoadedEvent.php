<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Event;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\Event\GenericEvent;
use HeyFrame\Core\Framework\Event\NestedEvent;
use HeyFrame\Core\Framework\Event\NestedEventCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @template TEntity of Entity
 *
 * @implements \IteratorAggregate<array-key, TEntity>
 */
#[Package('framework')]
class EntityLoadedEvent extends NestedEvent implements GenericEvent, \IteratorAggregate
{
    protected string $name;

    /**
     * @param TEntity[] $entities
     */
    public function __construct(
        protected EntityDefinition $definition,
        protected array $entities,
        protected Context $context
    ) {
        $this->name = $this->definition->getEntityName() . '.loaded';
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->entities);
    }

    /**
     * @return TEntity[]
     */
    public function getEntities(): array
    {
        return $this->entities;
    }

    public function getDefinition(): EntityDefinition
    {
        return $this->definition;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEvents(): ?NestedEventCollection
    {
        return null;
    }

    /**
     * @return list<string>
     */
    public function getIds(): array
    {
        $ids = [];

        foreach ($this->entities as $entity) {
            $ids[] = $entity->getUniqueIdentifier();
        }

        return $ids;
    }
}
