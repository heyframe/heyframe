<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Lifecycle\Update;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('framework')]
abstract class AbstractAppUpdater
{
    abstract public function updateApps(Context $context): void;

    abstract protected function getDecorated(): AbstractAppUpdater;
}
