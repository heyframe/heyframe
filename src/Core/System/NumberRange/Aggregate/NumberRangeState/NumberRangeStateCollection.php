<?php declare(strict_types=1);

namespace HeyFrame\Core\System\NumberRange\Aggregate\NumberRangeState;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<NumberRangeStateEntity>
 */
#[Package('framework')]
class NumberRangeStateCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'number_range_state_collection';
    }

    protected function getExpectedClass(): string
    {
        return NumberRangeStateEntity::class;
    }
}
