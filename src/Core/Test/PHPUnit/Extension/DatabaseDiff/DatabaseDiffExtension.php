<?php declare(strict_types=1);

namespace HeyFrame\Core\Test\PHPUnit\Extension\DatabaseDiff;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Kernel;
use HeyFrame\Core\Test\PHPUnit\Extension\DatabaseDiff\Subscriber\BeforeTestMethodCalledSubscriber;
use HeyFrame\Core\Test\PHPUnit\Extension\DatabaseDiff\Subscriber\TestFinishedSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

/**
 * @internal
 */
#[Package('framework')]
class DatabaseDiffExtension implements Extension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $dbState = new DbState(Kernel::getConnection());

        $facade->registerSubscribers(
            new BeforeTestMethodCalledSubscriber($dbState),
            new TestFinishedSubscriber($dbState)
        );
    }
}
