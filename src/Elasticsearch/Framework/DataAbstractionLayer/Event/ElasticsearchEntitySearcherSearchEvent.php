<?php declare(strict_types=1);

namespace HeyFrame\Elasticsearch\Framework\DataAbstractionLayer\Event;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Event\HeyFrameEvent;
use HeyFrame\Core\Framework\Log\Package;
use OpenSearchDSL\Search;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('framework')]
class ElasticsearchEntitySearcherSearchEvent extends Event implements HeyFrameEvent
{
    public function __construct(
        private readonly Search $search,
        private readonly EntityDefinition $definition,
        private readonly Criteria $criteria,
        private readonly Context $context
    ) {
    }

    public function getSearch(): Search
    {
        return $this->search;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getDefinition(): EntityDefinition
    {
        return $this->definition;
    }

    public function getCriteria(): Criteria
    {
        return $this->criteria;
    }
}
