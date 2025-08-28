<?php declare(strict_types=1);

namespace HeyFrame\Core\System\StateMachine\Aggregation\StateMachineState;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<StateMachineStateEntity>
 */
#[Package('checkout')]
class StateMachineStateCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'state_machine_state_collection';
    }

    protected function getExpectedClass(): string
    {
        return StateMachineStateEntity::class;
    }
}
