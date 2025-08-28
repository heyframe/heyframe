<?php declare(strict_types=1);

namespace HeyFrame\Core\Test\PHPUnit\Extension\DatabaseDiff\Subscriber;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Test\PHPUnit\Extension\DatabaseDiff\DbState;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\FinishedSubscriber;

/**
 * @internal
 */
#[Package('framework')]
class TestFinishedSubscriber implements FinishedSubscriber
{
    public function __construct(private readonly DbState $dbState)
    {
    }

    public function notify(Finished $event): void
    {
        $diff = $this->dbState->getDiff();

        if (!empty($diff)) {
            echo \PHP_EOL . $event->asString() . \PHP_EOL;

            print_r($diff);
        }
    }
}
