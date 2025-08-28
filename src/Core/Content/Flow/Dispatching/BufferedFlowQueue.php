<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Flow\Dispatching;

use HeyFrame\Core\Framework\Event\FlowEventAware;

/**
 * @experimental stableVersion:v6.8.0 feature:FLOW_EXECUTION_AFTER_BUSINESS_PROCESS
 */
class BufferedFlowQueue
{
    /**
     * @var array<FlowEventAware>
     */
    private array $bufferedFlows = [];

    public function queueFlow(FlowEventAware $flowEvent): void
    {
        $this->bufferedFlows[] = $flowEvent;
    }

    /**
     * @return array<FlowEventAware>
     */
    public function dequeueFlows(): array
    {
        $flows = $this->bufferedFlows;
        $this->bufferedFlows = [];

        return $flows;
    }

    public function isEmpty(): bool
    {
        return empty($this->bufferedFlows);
    }
}
