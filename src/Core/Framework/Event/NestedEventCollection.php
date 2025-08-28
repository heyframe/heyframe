<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Event;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Collection;

/**
 * @extends Collection<NestedEvent>
 */
#[Package('framework')]
class NestedEventCollection extends Collection
{
    public function getFlatEventList(): self
    {
        $events = [];

        foreach ($this->getIterator() as $event) {
            foreach ($event->getFlatEventList() as $item) {
                $events[] = $item;
            }
        }

        return new self($events);
    }

    public function getApiAlias(): string
    {
        return 'dal_nested_event_collection';
    }

    protected function getExpectedClass(): ?string
    {
        return NestedEvent::class;
    }
}
