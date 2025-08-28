<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Aggregate\ChannelAnalytics;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<ChannelAnalyticsEntity>
 */
#[Package('discovery')]
class ChannelAnalyticsCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'channel_analytics_collection';
    }

    protected function getExpectedClass(): string
    {
        return ChannelAnalyticsEntity::class;
    }
}
