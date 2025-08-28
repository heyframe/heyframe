<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Script\Execution\Awareness;

use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('framework')]
interface StoppableHook
{
    public function stopPropagation(): void;

    public function isPropagationStopped(): bool;
}
