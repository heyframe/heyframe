<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Test\Script\Execution;

use HeyFrame\Core\Framework\Script\Execution\Awareness\StoppableHook;
use HeyFrame\Core\Framework\Script\Execution\Awareness\StoppableHookTrait;

/**
 * @internal
 */
class StoppableTestHook extends TestHook implements StoppableHook
{
    use StoppableHookTrait;
}
