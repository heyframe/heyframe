<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Search;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResultCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('framework')]
interface EntityAggregatorInterface
{
    public function aggregate(EntityDefinition $definition, Criteria $criteria, Context $context): AggregationResultCollection;
}
