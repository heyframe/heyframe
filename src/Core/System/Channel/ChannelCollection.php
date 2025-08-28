<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<ChannelEntity>
 */
class ChannelCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'channel_collection';
    }

    protected function getExpectedClass(): string
    {
        return ChannelEntity::class;
    }
}
