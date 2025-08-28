<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\MessageQueue\Stats\Entity;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Collection;

/**
 * @internal
 *
 * @extends Collection<MessageTypeStatsEntity>
 */
#[Package('framework')]
class MessageTypeStatsCollection extends Collection
{
    protected function getExpectedClass(): ?string
    {
        return MessageTypeStatsEntity::class;
    }
}
