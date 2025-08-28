<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Event;

use HeyFrame\Core\Framework\Event\EventData\EventDataCollection;
use HeyFrame\Core\Framework\Log\Package;

#[Package('after-sales')]
interface FlowEventAware extends HeyFrameEvent
{
    public static function getAvailableData(): EventDataCollection;

    public function getName(): string;
}
