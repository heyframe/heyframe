<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Points\Aggregate\PointsLog;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<PointsLogEntity>
 */
#[Package('discovery')]
class PointsLogCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'points_log_collection';
    }

    protected function getExpectedClass(): string
    {
        return PointsLogEntity::class;
    }
}
