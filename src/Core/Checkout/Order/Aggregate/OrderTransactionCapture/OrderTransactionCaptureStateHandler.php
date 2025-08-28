<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Order\Aggregate\OrderTransactionCapture;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\StateMachine\Aggregation\StateMachineTransition\StateMachineTransitionActions;
use HeyFrame\Core\System\StateMachine\Exception\IllegalTransitionException;
use HeyFrame\Core\System\StateMachine\StateMachineException;
use HeyFrame\Core\System\StateMachine\StateMachineRegistry;
use HeyFrame\Core\System\StateMachine\Transition;

#[Package('checkout')]
class OrderTransactionCaptureStateHandler
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
    public function complete(string $transactionCaptureId, Context $context): void
    {
        $this->stateMachineRegistry->transition(
            new Transition(
                OrderTransactionCaptureDefinition::ENTITY_NAME,
                $transactionCaptureId,
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
    public function fail(string $transactionCaptureId, Context $context): void
    {
        $this->stateMachineRegistry->transition(
            new Transition(
                OrderTransactionCaptureDefinition::ENTITY_NAME,
                $transactionCaptureId,
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
    public function reopen(string $transactionCaptureId, Context $context): void
    {
        $this->stateMachineRegistry->transition(
            new Transition(
                OrderTransactionCaptureDefinition::ENTITY_NAME,
                $transactionCaptureId,
                StateMachineTransitionActions::ACTION_REOPEN,
                'stateId'
            ),
            $context
        );
    }
}
