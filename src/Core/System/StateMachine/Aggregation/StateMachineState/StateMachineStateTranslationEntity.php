<?php declare(strict_types=1);

namespace HeyFrame\Core\System\StateMachine\Aggregation\StateMachineState;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\TranslationEntity;
use HeyFrame\Core\Framework\Log\Package;

#[Package('checkout')]
class StateMachineStateTranslationEntity extends TranslationEntity
{
    use EntityCustomFieldsTrait;

    protected ?string $name = null;

    protected string $stateMachineStateId;

    protected ?StateMachineStateEntity $stateMachineState = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getStateMachineStateId(): string
    {
        return $this->stateMachineStateId;
    }

    public function setStateMachineStateId(string $stateMachineStateId): void
    {
        $this->stateMachineStateId = $stateMachineStateId;
    }

    public function getStateMachineState(): ?StateMachineStateEntity
    {
        return $this->stateMachineState;
    }

    public function setStateMachineState(StateMachineStateEntity $stateMachineState): void
    {
        $this->stateMachineState = $stateMachineState;
    }
}
