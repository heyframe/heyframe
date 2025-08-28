<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Search\AggregationResult\Metric;

use HeyFrame\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResult;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @final
 */
#[Package('framework')]
class SumResult extends AggregationResult
{
    public function __construct(
        string $name,
        protected float $sum
    ) {
        parent::__construct($name);
    }

    public function getSum(): float
    {
        return $this->sum;
    }
}
