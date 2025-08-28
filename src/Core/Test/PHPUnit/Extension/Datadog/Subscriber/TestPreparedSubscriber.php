<?php declare(strict_types=1);

namespace HeyFrame\Core\Test\PHPUnit\Extension\Datadog\Subscriber;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Test\PHPUnit\Extension\Common\TimeKeeper;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\Test\PreparedSubscriber;

/**
 * @internal
 */
#[Package('framework')]
class TestPreparedSubscriber implements PreparedSubscriber
{
    public function __construct(private readonly TimeKeeper $timeKeeper)
    {
    }

    public function notify(Prepared $event): void
    {
        $this->timeKeeper->start(
            $event->test()->id(),
            $event->telemetryInfo()->time()
        );
    }
}
