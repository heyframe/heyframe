<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Order\Aggregate\OrderTransactionCaptureRefund;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\StateMachine\Aggregation\StateMachineTransition\StateMachineTransitionActions;
use HeyFrame\Core\System\StateMachine\Exception\IllegalTransitionException;
use HeyFrame\Core\System\StateMachine\StateMachineException;
use HeyFrame\Core\System\StateMachine\StateMachineRegistry;
use HeyFrame\Core\System\StateMachine\Transition;

#[Package('checkout')]
class OrderTransactionCaptureRefundStateHandler
{
    /**
     * @internal
     */
    public function __construct(private readonly StateMachineRegistry $stateMachineRegistry)
    {
    }

    /**
     * @throws InconsistentCriteriaIdsException
     * @throws StateMachineException
     * @throws IllegalTransitionException
     */
    public function complete(string $transactionCaptureRefundId, Context $context): void
    {
        $this->stateMachineRegistry->transition(
            new Transition(
                OrderTransactionCaptureRefundDefinition::ENTITY_NAME,
                $transactionCaptureRefundId,
                StateMachineTransitionActions::ACTION_COMPLETE,
                'stateId'
            ),
            $context
        );
    }

    /**
     * @throws InconsistentCriteriaIdsException
     * @throws StateMachineException
     * @throws IllegalTransitionException
     */
    public function process(string $transactionCaptureRefundId, Context $context): void
    {
        $this->stateMachineRegistry->transition(
            new Transition(
                OrderTransactionCaptureRefundDefinition::ENTITY_NAME,
                $transactionCaptureRefundId,
                StateMachineTransitionActions::ACTION_PROCESS,
                'stateId'
            ),
            $context
        );
    }

    /**
     * @throws InconsistentCriteriaIdsException
     * @throws StateMachineException
     * @throws IllegalTransitionException
     */
    public function cancel(string $transactionCaptureRefundId, Context $context): void
    {
        $this->stateMachineRegistry->transition(
            new Transition(
                OrderTransactionCaptureRefundDefinition::ENTITY_NAME,
                $transactionCaptureRefundId,
                StateMachineTransitionActions::ACTION_CANCEL,
                'stateId'
            ),
            $context
        );
    }

    /**
     * @throws InconsistentCriteriaIdsException
     * @throws StateMachineException
     * @throws IllegalTransitionException
     */
    public function fail(string $transactionCaptureRefundId, Context $context): void
    {
        $this->stateMachineRegistry->transition(
            new Transition(
                OrderTransactionCaptureRefundDefinition::ENTITY_NAME,
                $transactionCaptureRefundId,
                StateMachineTransitionActions::ACTION_FAIL,
                'stateId'
            ),
            $context
        );
    }

    /**
     * @throws InconsistentCriteriaIdsException
     * @throws StateMachineException
     * @throws IllegalTransitionException
     */
    public function reopen(string $transactionCaptureRefundId, Context $context): void
    {
        $this->stateMachineRegistry->transition(
            new Transition(
                OrderTransactionCaptureRefundDefinition::ENTITY_NAME,
                $transactionCaptureRefundId,
                StateMachineTransitionActions::ACTION_REOPEN,
                'stateId'
            ),
            $context
        );
    }
}
