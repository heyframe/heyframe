<?php declare(strict_types=1);

namespace HeyFrame\Elasticsearch\Framework\Indexing\Event;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\HeyFrameEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Elasticsearch\Framework\AbstractElasticsearchDefinition;

#[Package('framework')]
class ElasticsearchIndexConfigEvent implements HeyFrameEvent
{
    /**
     * @param array<mixed> $config
     */
    public function __construct(
        private readonly string $indexName,
        private array $config,
        private readonly AbstractElasticsearchDefinition $definition,
        private readonly Context $context
    ) {
    }

    public function getIndexName(): string
    {
        return $this->indexName;
    }

    /**
     * @return array<mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    public function getDefinition(): AbstractElasticsearchDefinition
    {
        return $this->definition;
    }

    /**
     * @param array<mixed> $config
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
