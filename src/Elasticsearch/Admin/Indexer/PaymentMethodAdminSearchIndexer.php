<?php declare(strict_types=1);

namespace HeyFrame\Elasticsearch\Admin\Indexer;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use HeyFrame\Core\Checkout\Payment\Aggregate\PaymentMethodTranslation\PaymentMethodTranslationDefinition;
use HeyFrame\Core\Checkout\Payment\PaymentMethodCollection;
use HeyFrame\Core\Checkout\Payment\PaymentMethodDefinition;
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
final class PaymentMethodAdminSearchIndexer extends AbstractAdminIndexer
{
    /**
     * @internal
     *
     * @param EntityRepository<PaymentMethodCollection> $repository
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
        return PaymentMethodDefinition::ENTITY_NAME;
    }

    public function getName(): string
    {
        return 'payment-method-listing';
    }

    public function getIterator(): IterableQuery
    {
        return $this->factory->createIterator($this->getEntity(), null, $this->indexingBatchSize);
    }

    public function getUpdatedIds(EntityWrittenContainerEvent $event): array
    {
        $ids = [];

        $translations = $event->getPrimaryKeysWithPropertyChange(PaymentMethodTranslationDefinition::ENTITY_NAME, [
            'name',
        ]);

        foreach ($translations as $pks) {
            if (isset($pks['paymentMethodId'])) {
                $ids[] = $pks['paymentMethodId'];
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
            SELECT LOWER(HEX(payment_method.id)) as id,
                   GROUP_CONCAT(DISTINCT payment_method_translation.name SEPARATOR " ") as name
            FROM payment_method
                INNER JOIN payment_method_translation
                    ON payment_method.id = payment_method_translation.payment_method_id
            WHERE payment_method.id IN (:ids)
            GROUP BY payment_method.id
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
