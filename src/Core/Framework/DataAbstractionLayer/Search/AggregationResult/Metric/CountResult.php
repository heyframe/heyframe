<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Search\AggregationResult\Metric;

use HeyFrame\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResult;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @final
 */
#[Package('framework')]
class CountResult extends AggregationResult
{
    public function __construct(
        string $name,
        protected int $count
    ) {
        parent::__construct($name);
    }

    public function getCount(): int
    {
        return $this->count;
    }
}
