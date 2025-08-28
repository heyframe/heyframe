<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\MessageQueue\Stats\Entity;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Struct;

/**
 * @internal
 */
#[Package('framework')]
class MessageTypeStatsEntity extends Struct
{
    public function __construct(
        public readonly string $type,
        public readonly int $count,
    ) {
    }
}
