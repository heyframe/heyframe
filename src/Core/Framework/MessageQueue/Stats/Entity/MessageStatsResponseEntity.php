<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\MessageQueue\Stats\Entity;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Struct;

/**
 * @internal
 */
#[Package('framework')]
class MessageStatsResponseEntity extends Struct
{
    public function __construct(
        public readonly bool $enabled,
        public readonly ?MessageStatsEntity $stats = null,
    ) {
    }
}
