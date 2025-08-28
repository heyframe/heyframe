<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Event;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\JsonSerializableTrait;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('framework')]
abstract class NestedEvent extends Event implements HeyFrameEvent
{
    use JsonSerializableTrait;

    public function getEvents(): ?NestedEventCollection
    {
        return null;
    }

    public function getFlatEventList(): NestedEventCollection
    {
        $events = [$this];

        if (!$nestedEvents = $this->getEvents()) {
            return new NestedEventCollection($events);
        }

        foreach ($nestedEvents as $event) {
            $events[] = $event;
            foreach ($event->getFlatEventList() as $item) {
                $events[] = $item;
            }
        }

        return new NestedEventCollection($events);
    }
}
