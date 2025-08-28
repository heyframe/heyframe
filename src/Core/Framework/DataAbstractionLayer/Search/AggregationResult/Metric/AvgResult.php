<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Search\AggregationResult\Metric;

use HeyFrame\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResult;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @final
 */
#[Package('framework')]
class AvgResult extends AggregationResult
{
    public function __construct(
        string $name,
        protected float $avg
    ) {
        parent::__construct($name);
    }

    public function getAvg(): float
    {
        return $this->avg;
    }
}
