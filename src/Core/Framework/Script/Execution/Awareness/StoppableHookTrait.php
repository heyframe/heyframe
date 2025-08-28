<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Script\Execution\Awareness;

use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('framework')]
trait StoppableHookTrait
{
    protected bool $isPropagationStopped = false;

    public function stopPropagation(): void
    {
        $this->isPropagationStopped = true;
    }

    public function isPropagationStopped(): bool
    {
        return $this->isPropagationStopped;
    }
}
