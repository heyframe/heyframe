<?php declare(strict_types=1);

namespace HeyFrame\Core\Test\PHPUnit\Extension\DatabaseDiff\Subscriber;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Test\PHPUnit\Extension\DatabaseDiff\DbState;
use PHPUnit\Event\Test\BeforeTestMethodCalled;
use PHPUnit\Event\Test\BeforeTestMethodCalledSubscriber as BeforeTestMethodCalledSubscriberInterface;

/**
 * @internal
 */
#[Package('framework')]
class BeforeTestMethodCalledSubscriber implements BeforeTestMethodCalledSubscriberInterface
{
    public function __construct(private readonly DbState $dbState)
    {
    }

    public function notify(BeforeTestMethodCalled $event): void
    {
        $this->dbState->rememberCurrentDbState();
    }
}
