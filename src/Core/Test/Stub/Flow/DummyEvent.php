<?php declare(strict_types=1);

namespace HeyFrame\Core\Test\Stub\Flow;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\EventData\EventDataCollection;
use HeyFrame\Core\Framework\Event\FlowEventAware;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('framework')]
class DummyEvent extends Event implements FlowEventAware
{
    public static function getAvailableData(): EventDataCollection
    {
        return new EventDataCollection();
    }

    public function getName(): string
    {
        return 'dummy.event';
    }

    public function getContext(): Context
    {
        return Context::createDefaultContext();
    }
}
