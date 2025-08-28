<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Aggregate\ChannelDomain;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<ChannelDomainEntity>
 */
#[Package('discovery')]
class ChannelDomainCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'channel_domain_collection';
    }

    protected function getExpectedClass(): string
    {
        return ChannelDomainEntity::class;
    }
}
