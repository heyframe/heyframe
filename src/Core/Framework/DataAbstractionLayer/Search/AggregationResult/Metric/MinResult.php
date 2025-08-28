<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Search\AggregationResult\Metric;

use HeyFrame\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResult;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @final
 */
#[Package('framework')]
class MinResult extends AggregationResult
{
    public function __construct(
        string $name,
        protected float|int|string|null $min
    ) {
        parent::__construct($name);
    }

    public function getMin(): float|int|string|null
    {
        return $this->min;
    }
}
