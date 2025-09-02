<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Points;

use HeyFrame\Core\Checkout\Wallet\WalletEntity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<WalletEntity>
 */
#[Package('checkout')]
class PointsCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'points_collection';
    }

    protected function getExpectedClass(): string
    {
        return PointsEntity::class;
    }
}
