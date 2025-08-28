<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Flow\Dispatching;

use HeyFrame\Core\Content\Flow\Dispatching\Struct\Sequence;

class FlowState
{
    public string $flowId;

    public bool $stop = false;

    public Sequence $currentSequence;

    public bool $delayed = false;

    public function getSequenceId(): string
    {
        return $this->currentSequence->sequenceId;
    }
}
