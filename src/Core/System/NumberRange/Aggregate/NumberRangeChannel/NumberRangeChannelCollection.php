<?php declare(strict_types=1);

namespace HeyFrame\Core\System\NumberRange\Aggregate\NumberRangeChannel;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<NumberRangeChannelEntity>
 */
#[Package('framework')]
class NumberRangeChannelCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'number_range_channel_collection';
    }

    protected function getExpectedClass(): string
    {
        return NumberRangeChannelEntity::class;
    }
}
