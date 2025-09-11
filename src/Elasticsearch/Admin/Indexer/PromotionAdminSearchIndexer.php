<?php declare(strict_types=1);

namespace HeyFrame\Elasticsearch\Admin\Indexer;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionTranslation\PromotionTranslationDefinition;
use HeyFrame\Core\Checkout\Promotion\PromotionCollection;
use HeyFrame\Core\Checkout\Promotion\PromotionDefinition;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Dbal\Common\IterableQuery;
use HeyFrame\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Uuid\Uuid;

/**
 * @final
 */
#[Package('inventory')]
class PromotionAdminSearchIndexer extends AbstractAdminIndexer
{
    /**
     * @internal
     *
     * @param EntityRepository<PromotionCollection> $repository
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
        return PromotionDefinition::ENTITY_NAME;
    }

    public function getName(): string
    {
        return 'promotion-listing';
    }

    public function getIterator(): IterableQuery
    {
        return $this->factory->createIterator($this->getEntity(), null, $this->indexingBatchSize);
    }

    public function getUpdatedIds(EntityWrittenContainerEvent $event): array
    {
        $ids = [];

        $translations = $event->getPrimaryKeysWithPropertyChange(PromotionTranslationDefinition::ENTITY_NAME, [
            'name',
        ]);

        foreach ($translations as $pks) {
            if (isset($pks['promotionId'])) {
                $ids[] = $pks['promotionId'];
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
            SELECT LOWER(HEX(promotion.id)) as id,
                   GROUP_CONCAT(DISTINCT promotion_translation.name SEPARATOR " ") as name
            FROM promotion
                INNER JOIN promotion_translation
                    ON promotion.id = promotion_translation.promotion_id
            WHERE promotion.id IN (:ids)
            GROUP BY promotion.id
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
            $text = \implode(' ', array_filter($row));
            $mapped[$id] = ['id' => $id, 'text' => \strtolower($text)];
        }

        return $mapped;
    }
}
