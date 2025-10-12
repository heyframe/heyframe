<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Points;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<PointsEntity>
 */
#[Package('discovery')]
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
