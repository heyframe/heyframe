<?php declare(strict_types=1);

namespace HeyFrame\Core\System\StateMachine\Aggregation\StateMachineTransition;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<StateMachineTransitionEntity>
 */
#[Package('checkout')]
class StateMachineTransitionCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'state_machine_transition_collection';
    }

    protected function getExpectedClass(): string
    {
        return StateMachineTransitionEntity::class;
    }
}
