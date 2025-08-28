<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Detail;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Content\Product\Stock\AbstractStockStorage;
use HeyFrame\Core\Content\Product\Stock\StockLoadRequest;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Doctrine\FetchModeHelper;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('inventory')]
class AvailableCombinationLoader extends AbstractAvailableCombinationLoader
{
    /**
     * @internal
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly AbstractStockStorage $stockStorage
    ) {
    }

    public function getDecorated(): AbstractAvailableCombinationLoader
    {
        throw new DecorationPatternException(self::class);
    }

    public function loadCombinations(string $productId, ChannelContext $channelContext): AvailableCombinationResult
    {
        $combinations = $this->getCombinations(
            $productId,
            $channelContext->getContext(),
            $channelContext->getChannelId()
        );

        $stocks = $this->stockStorage->load(
            new StockLoadRequest(array_keys($combinations)),
            $channelContext
        );

        $result = new AvailableCombinationResult();
        foreach ($combinations as $id => $combination) {
            try {
                $options = json_decode((string) $combination['options'], true, 512, \JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                continue;
            }

            $available = (bool) $combination['available'];
            $stockData = $stocks->getStockForProductId($id);

            if ($stockData !== null) {
                $available = $stockData->available;
            }

            $result->addCombination($options, $available);
        }

        return $result;
    }

    /**
     * @return array<string, array{options: string, available: int, productNumber: string}>
     */
    private function getCombinations(string $productId, Context $context, string $channelId): array
    {
        $query = $this->connection->createQueryBuilder();
        $query->from('product');
        $query->leftJoin('product', 'product', 'parent', 'product.parent_id = parent.id');

        $query->andWhere('product.parent_id = :id');
        $query->andWhere('product.version_id = :versionId');
        $query->andWhere('IFNULL(product.active, parent.active) = :active');
        $query->andWhere('product.option_ids IS NOT NULL');

        $query->setParameter('id', Uuid::fromHexToBytes($productId));
        $query->setParameter('versionId', Uuid::fromHexToBytes($context->getVersionId()));
        $query->setParameter('active', true);

        $query->innerJoin('product', 'product_visibility', 'visibilities', 'product.visibilities = visibilities.product_id');
        $query->andWhere('visibilities.channel_id = :channelId');
        $query->setParameter('channelId', Uuid::fromHexToBytes($channelId));

        $query->select(
            'LOWER(HEX(product.id))',
            'product.option_ids as options',
            'product.product_number as productNumber',
            'product.available',
        );

        $combinations = $query->executeQuery()->fetchAllAssociative();

        /** @var array<string, array{options: string, available: int, productNumber: string}> $unique */
        $unique = FetchModeHelper::groupUnique($combinations);

        return $unique;
    }
}
