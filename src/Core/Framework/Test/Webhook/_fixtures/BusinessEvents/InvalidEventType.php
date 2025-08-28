<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Test\Webhook\_fixtures\BusinessEvents;

use HeyFrame\Core\Framework\Event\EventData\EventDataType;

/**
 * @internal
 */
class InvalidEventType implements EventDataType
{
    public function toArray(): array
    {
        return [
            'type' => 'invalid',
        ];
    }
}
