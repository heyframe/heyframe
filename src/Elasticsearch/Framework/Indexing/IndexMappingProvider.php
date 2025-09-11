<?php declare(strict_types=1);

namespace HeyFrame\Elasticsearch\Framework\Indexing;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Elasticsearch\Framework\AbstractElasticsearchDefinition;

#[Package('framework')]
class IndexMappingProvider
{
    /**
     * @internal
     *
     * @param array<mixed> $mapping
     */
    public function __construct(
        private readonly array $mapping,
    ) {
    }

    /**
     * @return array<mixed>
     */
    public function build(AbstractElasticsearchDefinition $definition, Context $context): array
    {
        $mapping = $definition->getMapping($context);

        return array_merge_recursive($mapping, $this->mapping);
    }
}
