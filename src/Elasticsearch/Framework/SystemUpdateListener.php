<?php declare(strict_types=1);

namespace HeyFrame\Elasticsearch\Framework;

use HeyFrame\Core\Framework\Adapter\Storage\AbstractKeyValueStorage;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Update\Event\UpdatePostFinishEvent;
use HeyFrame\Elasticsearch\Framework\Indexing\ElasticsearchIndexer;
use HeyFrame\Elasticsearch\Framework\Indexing\ElasticsearchIndexingMessage;
use HeyFrame\Elasticsearch\Framework\Indexing\IndexMappingUpdater;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @internal
 */
#[Package('framework')]
#[AsEventListener]
class SystemUpdateListener
{
    public const CONFIG_KEY = 'elasticsearch.indexing.entities';

    /**
     * @internal
     */
    public function __construct(
        private readonly AbstractKeyValueStorage $storage,
        private readonly ElasticsearchIndexer $indexer,
        private readonly MessageBusInterface $messageBus,
        private readonly IndexMappingUpdater $mappingUpdater
    ) {
    }

    public function __invoke(UpdatePostFinishEvent $event): void
    {
        $this->mappingUpdater->update($event->getContext());

        $entitiesToReindex = $this->storage->get(self::CONFIG_KEY, []);

        if (empty($entitiesToReindex)) {
            return;
        }

        $messagesToDispatch = [];
        $offset = null;
        while ($message = $this->indexer->iterate($offset)) {
            $offset = $message->getOffset();

            $messagesToDispatch[] = $message;
        }

        $lastMessage = end($messagesToDispatch);

        if (!$lastMessage instanceof ElasticsearchIndexingMessage) {
            return;
        }

        $lastMessage->markAsLastMessage();

        foreach ($messagesToDispatch as $message) {
            $this->messageBus->dispatch($message);
        }

        $this->storage->remove(self::CONFIG_KEY);
    }
}
