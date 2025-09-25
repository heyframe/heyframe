<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Navigation\DataAbstractionLayer;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Content\Navigation\Event\NavigationIndexerEvent;
use HeyFrame\Core\Content\Navigation\NavigationCollection;
use HeyFrame\Core\Content\Navigation\NavigationDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Dbal\Common\IterableQuery;
use HeyFrame\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use HeyFrame\Core\Framework\DataAbstractionLayer\Doctrine\RetryableTransaction;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use HeyFrame\Core\Framework\DataAbstractionLayer\Indexing\ChildCountUpdater;
use HeyFrame\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexer;
use HeyFrame\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexerRegistry;
use HeyFrame\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexingMessage;
use HeyFrame\Core\Framework\DataAbstractionLayer\Indexing\TreeUpdater;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Uuid\Uuid;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Package('discovery')]
class NavigationIndexer extends EntityIndexer
{
    final public const CHILD_COUNT_UPDATER = 'navigation.child-count';
    final public const TREE_UPDATER = 'navigation.tree';
    private const UPDATE_IDS_CHUNK_SIZE = 50;

    /**
     * @internal
     *
     * @param EntityRepository<NavigationCollection> $repository
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly IteratorFactory $iteratorFactory,
        private readonly EntityRepository $repository,
        private readonly ChildCountUpdater $childCountUpdater,
        private readonly TreeUpdater $treeUpdater,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function getName(): string
    {
        return 'navigation.indexer';
    }

    public function getTotal(): int
    {
        return $this->getIterator(null)->fetchCount();
    }

    public function iterate(?array $offset): ?EntityIndexingMessage
    {
        $iterator = $this->getIterator($offset);

        $ids = $iterator->fetch();

        if (empty($ids)) {
            return null;
        }

        return new NavigationIndexingMessage(
            data: array_values($ids),
            offset: $iterator->getOffset()
        );
    }

    public function update(EntityWrittenContainerEvent $event): ?EntityIndexingMessage
    {
        $navigationEvent = $event->getEventByEntityName(NavigationDefinition::ENTITY_NAME);

        if (!$navigationEvent) {
            return null;
        }

        $ids = $navigationEvent->getIds();
        $idsWithChangedParentIds = [];
        foreach ($navigationEvent->getWriteResults() as $result) {
            if (!$result->getExistence()) {
                continue;
            }
            $state = $result->getExistence()->getState();

            if (isset($state['parent_id'])) {
                $ids[] = Uuid::fromBytesToHex($state['parent_id']);
            }

            $payload = $result->getPayload();
            if (\array_key_exists('parentId', $payload)) {
                if ($payload['parentId'] !== null) {
                    $ids[] = $payload['parentId'];
                }
                $idsWithChangedParentIds[] = $payload['id'];
            }
        }

        if (empty($ids)) {
            return null;
        }

        if ($idsWithChangedParentIds !== []) {
            $this->treeUpdater->batchUpdate(
                $idsWithChangedParentIds,
                NavigationDefinition::ENTITY_NAME,
                $event->getContext(),
                true
            );
        }

        $children = $this->fetchChildren($ids, $event->getContext()->getVersionId());
        $ids = array_unique(array_merge($ids, $children));

        $chunks = \array_chunk($ids, self::UPDATE_IDS_CHUNK_SIZE);
        $idsForReturnedMessage = array_shift($chunks);

        foreach ($chunks as $chunk) {
            $childrenIndexingMessage = new NavigationIndexingMessage($chunk, null, $event->getContext());
            $childrenIndexingMessage->setIndexer($this->getName());
            EntityIndexerRegistry::addSkips($childrenIndexingMessage, $event->getContext());

            $this->messageBus->dispatch($childrenIndexingMessage);
        }

        return new NavigationIndexingMessage($idsForReturnedMessage, null, $event->getContext());
    }

    public function handle(EntityIndexingMessage $message): void
    {
        $ids = $message->getData();
        if (!\is_array($ids)) {
            return;
        }

        $ids = array_values(array_unique(array_filter($ids)));
        if (empty($ids)) {
            return;
        }

        $context = $message->getContext();

        RetryableTransaction::retryable($this->connection, function () use ($message, $ids, $context): void {
            if ($message->allow(self::CHILD_COUNT_UPDATER)) {
                // listen to parent id changes
                $this->childCountUpdater->update(NavigationDefinition::ENTITY_NAME, $ids, $context);
            }

            if ($message->allow(self::TREE_UPDATER)) {
                $this->treeUpdater->batchUpdate(
                    $ids,
                    NavigationDefinition::ENTITY_NAME,
                    $context,
                    !$message->isFullIndexing
                );
            }
        });

        $this->eventDispatcher->dispatch(new NavigationIndexerEvent($ids, $context, $message->getSkip(), $message->isFullIndexing));
    }

    public function getOptions(): array
    {
        return [
            self::CHILD_COUNT_UPDATER,
            self::TREE_UPDATER,
        ];
    }

    public function getDecorated(): EntityIndexer
    {
        throw new DecorationPatternException(static::class);
    }

    /**
     * @param array<string> $navigationIds
     *
     * @return array<string>
     */
    private function fetchChildren(array $navigationIds, string $versionId): array
    {
        $query = $this->connection->createQueryBuilder();
        $query->select('DISTINCT LOWER(HEX(navigation.id))');
        $query->from('navigation');

        $wheres = [];
        foreach ($navigationIds as $id) {
            $key = 'path' . $id;
            $wheres[] = 'navigation.path LIKE :' . $key;
            $query->setParameter($key, '%|' . $id . '|%');
        }

        $query->andWhere('(' . implode(' OR ', $wheres) . ')');
        $query->andWhere('navigation.version_id = :version');
        $query->setParameter('version', Uuid::fromHexToBytes($versionId));

        return $query->executeQuery()->fetchFirstColumn();
    }

    /**
     * @param array{offset: int|null}|null $offset
     */
    private function getIterator(?array $offset): IterableQuery
    {
        return $this->iteratorFactory->createIterator($this->repository->getDefinition(), $offset);
    }
}
