<?php declare(strict_types=1);

namespace HeyFrame\Elasticsearch\Framework\DataAbstractionLayer\Event;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;
use HeyFrame\Core\Framework\Event\HeyFrameEvent;
use HeyFrame\Core\Framework\Log\Package;
use OpenSearchDSL\Search;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @codeCoverageIgnore
 */
#[Package('framework')]
class ElasticsearchEntitySearcherSearchedEvent extends Event implements HeyFrameEvent
{
    /**
     * @param array<string, mixed> $response
     */
    public function __construct(
        public readonly IdSearchResult $result,
        public readonly Search $search,
        public readonly EntityDefinition $definition,
        public readonly Criteria $criteria,
        private readonly Context $context,
        public readonly array $response = [],
    ) {
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
