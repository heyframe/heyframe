<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Event\EventData;

use HeyFrame\Core\Framework\Log\Package;

#[Package('fundamentals@after-sales')]
interface EventDataType
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
