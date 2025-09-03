<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Points\Aggregate\PointsTransactions;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<PointsTransactionsEntity>
 */
#[Package('checkout')]
class PointsTransactionsCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'points_transactions_collection';
    }

    protected function getExpectedClass(): string
    {
        return PointsTransactionsEntity::class;
    }
}
