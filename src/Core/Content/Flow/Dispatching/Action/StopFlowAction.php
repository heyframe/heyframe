<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Flow\Dispatching\Action;

use HeyFrame\Core\Content\Flow\Dispatching\DelayableAction;
use HeyFrame\Core\Content\Flow\Dispatching\StorableFlow;

/**
 * @internal
 */
class StopFlowAction extends FlowAction implements DelayableAction
{
    public static function getName(): string
    {
        return 'action.stop.flow';
    }

    /**
     * @return array<int, string|null>
     */
    public function requirements(): array
    {
        return [];
    }

    public function handleFlow(StorableFlow $flow): void
    {
        $flow->stop();
    }
}
