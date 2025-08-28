<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Search\AggregationResult\Metric;

use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResult;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @final
 *
 * @template TEntityCollection of EntityCollection
 */
#[Package('framework')]
class EntityResult extends AggregationResult
{
    /**
     * @param TEntityCollection $entities
     */
    public function __construct(string $name, protected EntityCollection $entities)
    {
        parent::__construct($name);
    }

    /**
     * @return TEntityCollection
     */
    public function getEntities(): EntityCollection
    {
        return $this->entities;
    }

    public function add(Entity $entity): void
    {
        $this->entities->add($entity);
    }
}
