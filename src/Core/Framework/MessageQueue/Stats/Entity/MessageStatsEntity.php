<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\MessageQueue\Stats\Entity;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Struct;

/**
 * @internal
 */
#[Package('framework')]
class MessageStatsEntity extends Struct
{
    public function __construct(
        public readonly int $totalMessagesProcessed,
        public readonly \DateTimeInterface $processedSince,
        public readonly float $averageTimeInQueue,
        public readonly MessageTypeStatsCollection $messageTypeStats,
    ) {
    }
}
