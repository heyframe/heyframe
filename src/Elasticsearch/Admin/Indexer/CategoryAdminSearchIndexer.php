<?php declare(strict_types=1);

namespace HeyFrame\Elasticsearch\Admin\Indexer;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use HeyFrame\Core\Content\Category\Aggregate\CategoryTag\CategoryTagDefinition;
use HeyFrame\Core\Content\Category\Aggregate\CategoryTranslation\CategoryTranslationDefinition;
use HeyFrame\Core\Content\Category\CategoryCollection;
use HeyFrame\Core\Content\Category\CategoryDefinition;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Dbal\Common\IterableQuery;
use HeyFrame\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Uuid\Uuid;

#[Package('inventory')]
final class CategoryAdminSearchIndexer extends AbstractAdminIndexer
{
    /**
     * @internal
     *
     * @param EntityRepository<CategoryCollection> $repository
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly IteratorFactory $factory,
        private readonly EntityRepository $repository,
        private readonly int $indexingBatchSize
    ) {
    }

    public function getDecorated(): AbstractAdminIndexer
    {
        throw new DecorationPatternException(self::class);
    }

    public function getEntity(): string
    {
        return CategoryDefinition::ENTITY_NAME;
    }

    public function getName(): string
    {
        return 'category-listing';
    }

    public function getIterator(): IterableQuery
    {
        return $this->factory->createIterator($this->getEntity(), null, $this->indexingBatchSize);
    }

    public function getUpdatedIds(EntityWrittenContainerEvent $event): array
    {
        $ids = [];

        $translations = $event->getPrimaryKeysWithPropertyChange(CategoryTranslationDefinition::ENTITY_NAME, [
            'name',
        ]);

        $tags = $event->getPrimaryKeysWithPropertyChange(CategoryTagDefinition::ENTITY_NAME, [
            'tagId',
        ]);

        foreach (array_merge($translations, $tags) as $pks) {
            if (isset($pks['categoryId'])) {
                $ids[] = $pks['categoryId'];
            }
        }

        return \array_values(\array_unique($ids));
    }

    public function globalData(array $result, Context $context): array
    {
        $ids = array_column($result['hits'], 'id');

        return [
            'total' => (int) $result['total'],
            'data' => $this->repository->search(new Criteria($ids), $context)->getEntities(),
        ];
    }

    public function fetch(array $ids): array
    {
        $data = $this->connection->fetchAllAssociative(
            '
            SELECT LOWER(HEX(category.id)) as id,
                   GROUP_CONCAT(DISTINCT category_translation.name SEPARATOR " ") as name,
                   GROUP_CONCAT(DISTINCT tag.name SEPARATOR " ") as tags
            FROM category
                INNER JOIN category_translation
                    ON category_translation.category_id = category.id
                LEFT JOIN category_tag
                    ON category_tag.category_id = category.id
                LEFT JOIN tag
                    ON category_tag.tag_id = tag.id
            WHERE category.id IN (:ids)
            GROUP BY category.id
            ',
            [
                'ids' => Uuid::fromHexToBytesList($ids),
            ],
            [
                'ids' => ArrayParameterType::BINARY,
            ]
        );

        $mapped = [];
        foreach ($data as $row) {
            $id = (string) $row['id'];
            $text = \implode(' ', array_filter(array_unique(array_values($row))));
            $mapped[$id] = ['id' => $id, 'text' => \strtolower($text)];
        }

        return $mapped;
    }
}
