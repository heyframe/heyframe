<?php declare(strict_types=1);

namespace HeyFrame\Elasticsearch\Framework\DataAbstractionLayer;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResultCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
abstract class AbstractElasticsearchAggregationHydrator
{
    abstract public function getDecorated(): AbstractElasticsearchAggregationHydrator;

    abstract public function hydrate(EntityDefinition $definition, Criteria $criteria, Context $context, array $result): AggregationResultCollection;
}
