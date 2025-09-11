<?php declare(strict_types=1);

namespace HeyFrame\Elasticsearch\Framework\Indexing\Event;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Elasticsearch\Framework\AbstractElasticsearchDefinition;

#[Package('framework')]
class ElasticsearchIndexCreatedEvent
{
    public function __construct(
        private readonly string $indexName,
        private readonly AbstractElasticsearchDefinition $definition
    ) {
    }

    public function getIndexName(): string
    {
        return $this->indexName;
    }

    public function getDefinition(): AbstractElasticsearchDefinition
    {
        return $this->definition;
    }
}
