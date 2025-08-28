<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Aggregate\ChannelType;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelCollection;

/**
 * @extends EntityCollection<ChannelTypeEntity>
 */
#[Package('discovery')]
class ChannelTypeCollection extends EntityCollection
{
    public function getChannels(): ChannelCollection
    {
        return new ChannelCollection(
            $this->fmap(fn (ChannelTypeEntity $channel) => $channel->getChannels())
        );
    }

    public function getApiAlias(): string
    {
        return 'channel_type_collection';
    }

    protected function getExpectedClass(): string
    {
        return ChannelTypeEntity::class;
    }
}
