<?php declare(strict_types=1);

namespace HeyFrame\Elasticsearch\Framework\DataAbstractionLayer\Event;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResultCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Event\HeyFrameEvent;
use HeyFrame\Core\Framework\Log\Package;
use OpenSearchDSL\Search;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @codeCoverageIgnore
 */
#[Package('framework')]
class ElasticsearchEntityAggregatorSearchedEvent extends Event implements HeyFrameEvent
{
    public function __construct(
        public readonly AggregationResultCollection $result,
        public readonly Search $search,
        public readonly EntityDefinition $definition,
        public readonly Criteria $criteria,
        private readonly Context $context
    ) {
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
