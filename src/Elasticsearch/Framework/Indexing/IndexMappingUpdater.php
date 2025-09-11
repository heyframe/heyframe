<?php declare(strict_types=1);

namespace HeyFrame\Elasticsearch\Framework\Indexing;

use HeyFrame\Core\Framework\Adapter\Storage\AbstractKeyValueStorage;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Elasticsearch\Framework\ElasticsearchHelper;
use HeyFrame\Elasticsearch\Framework\ElasticsearchRegistry;
use HeyFrame\Elasticsearch\Framework\SystemUpdateListener;
use HeyFrame\Elasticsearch\Product\ElasticsearchProductException;
use OpenSearch\Client;
use OpenSearch\Common\Exceptions\BadRequest400Exception;
use OpenSearch\Common\Exceptions\Missing404Exception;

#[Package('framework')]
class IndexMappingUpdater
{
    /**
     * @internal
     */
    public function __construct(
        private readonly ElasticsearchRegistry $registry,
        private readonly ElasticsearchHelper $elasticsearchHelper,
        private readonly Client $client,
        private readonly IndexMappingProvider $indexMappingProvider,
        private readonly AbstractKeyValueStorage $storage
    ) {
    }

    public function update(Context $context): void
    {
        if (!$this->elasticsearchHelper->allowIndexing()) {
            return;
        }

        $entitiesToReindex = $this->storage->get(SystemUpdateListener::CONFIG_KEY, []) ?? [];

        if (\is_string($entitiesToReindex)) {
            $entitiesToReindex = \json_decode($entitiesToReindex, true);
        }

        if (!\is_array($entitiesToReindex)) {
            $entitiesToReindex = [];
        }

        foreach ($this->registry->getDefinitions() as $definition) {
            $indexName = $this->elasticsearchHelper->getIndexName($definition->getEntityDefinition());

            try {
                $this->client->indices()->putMapping([
                    'index' => $indexName,
                    'body' => $this->indexMappingProvider->build($definition, $context),
                ]);
            } catch (BadRequest400Exception $exception) {
                if (str_contains($exception->getMessage(), 'cannot be changed from type') || str_contains($exception->getMessage(), 'can\'t merge a non object mapping')) {
                    $entitiesToReindex[] = $definition->getEntityDefinition()->getEntityName();

                    $exception = ElasticsearchProductException::cannotChangeFieldType($exception);
                }

                $this->elasticsearchHelper->logAndThrowException($exception);
            } catch (Missing404Exception $exception) {
                $this->elasticsearchHelper->logAndThrowException($exception);
            }
        }

        if (!empty($entitiesToReindex)) {
            $this->storage->set(SystemUpdateListener::CONFIG_KEY, \array_values(\array_unique($entitiesToReindex)));
        }
    }
}
