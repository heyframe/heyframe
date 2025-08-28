<?php declare(strict_types=1);

namespace HeyFrame\Core\System\NumberRange\Aggregate\NumberRangeType;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<NumberRangeTypeEntity>
 */
#[Package('framework')]
class NumberRangeTypeCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'number_range_type_collection';
    }

    protected function getExpectedClass(): string
    {
        return NumberRangeTypeEntity::class;
    }
}
