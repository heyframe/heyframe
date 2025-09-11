<?php declare(strict_types=1);

namespace HeyFrame\Elasticsearch\Admin\Indexer;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Dbal\Common\IterableQuery;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use HeyFrame\Core\Framework\Log\Package;
use OpenSearchDSL\Search;

#[Package('inventory')]
abstract class AbstractAdminIndexer
{
    abstract public function getDecorated(): self;

    abstract public function getName(): string;

    abstract public function getEntity(): string;

    /**
     * @param array<string, array<string, array<string, string>>> $mapping
     *
     * @return array<string, array<string, array<string, string>>>
     */
    public function mapping(array $mapping): array
    {
        return $mapping;
    }

    abstract public function getIterator(): IterableQuery;

    /**
     * @return array<string>
     */
    public function getUpdatedIds(EntityWrittenContainerEvent $event): array
    {
        return $event->getPrimaryKeys($this->getEntity());
    }

    /**
     * @param array<string> $ids
     *
     * @return array<string, array{id:string, text:string}>
     */
    abstract public function fetch(array $ids): array;

    /**
     * @param array<string, mixed> $result
     *
     * @return array{total:int, data: EntityCollection<covariant Entity>}
     *
     * Returns EntityCollection<Entity> and their total by ids in the result parameter
     */
    abstract public function globalData(array $result, Context $context): array;

    public function globalCriteria(string $term, Search $criteria): Search
    {
        return $criteria;
    }
}
