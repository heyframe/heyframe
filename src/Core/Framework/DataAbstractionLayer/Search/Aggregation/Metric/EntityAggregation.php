<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Search\Aggregation\Metric;

use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Aggregation\Aggregation;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @final
 */
#[Package('framework')]
class EntityAggregation extends Aggregation
{
    public function __construct(
        string $name,
        string $field,
        protected readonly string $entity
    ) {
        parent::__construct($name, $field);
    }

    public function getEntity(): string
    {
        return $this->entity;
    }
}
