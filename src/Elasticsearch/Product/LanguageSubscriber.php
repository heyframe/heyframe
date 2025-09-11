<?php declare(strict_types=1);

namespace HeyFrame\Elasticsearch\Product;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityWriteResult;
use HeyFrame\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Elasticsearch\Framework\ElasticsearchHelper;
use HeyFrame\Elasticsearch\Framework\ElasticsearchRegistry;
use OpenSearch\Client;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 * When a language is created, we need to trigger an indexing for that
 */
#[Package('framework')]
class LanguageSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ElasticsearchHelper $elasticsearchHelper,
        private readonly ElasticsearchRegistry $registry,
        private readonly Client $client,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'language.written' => 'onLanguageWritten',
        ];
    }

    public function onLanguageWritten(EntityWrittenEvent $event): void
    {
        if (!$this->elasticsearchHelper->allowIndexing()) {
            return;
        }

        $context = $event->getContext();

        foreach ($event->getWriteResults() as $writeResult) {
            if ($writeResult->getOperation() !== EntityWriteResult::OPERATION_INSERT) {
                continue;
            }

            foreach ($this->registry->getDefinitions() as $definition) {
                $indexName = $this->elasticsearchHelper->getIndexName($definition->getEntityDefinition());

                // index doesn't exist, don't need to do anything
                if (!$this->client->indices()->exists(['index' => $indexName])) {
                    continue;
                }

                $this->client->indices()->putMapping([
                    'index' => $indexName,
                    'body' => [
                        'properties' => $definition->getMapping($context)['properties'],
                    ],
                ]);
            }
        }
    }
}
