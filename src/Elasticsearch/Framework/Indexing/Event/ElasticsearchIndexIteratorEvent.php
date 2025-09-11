<?php declare(strict_types=1);

namespace HeyFrame\Elasticsearch\Framework\Indexing\Event;

use HeyFrame\Core\Framework\DataAbstractionLayer\Dbal\Common\IterableQuery;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Elasticsearch\Framework\AbstractElasticsearchDefinition;

/**
 * @codeCoverageIgnore
 */
#[Package('framework')]
class ElasticsearchIndexIteratorEvent
{
    public function __construct(
        public readonly AbstractElasticsearchDefinition $elasticsearchDefinition,
        public IterableQuery $iterator,
    ) {
    }
}
