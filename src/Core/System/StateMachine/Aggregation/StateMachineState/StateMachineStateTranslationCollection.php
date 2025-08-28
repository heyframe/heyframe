<?php declare(strict_types=1);

namespace HeyFrame\Core\System\StateMachine\Aggregation\StateMachineState;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<StateMachineStateTranslationEntity>
 */
#[Package('checkout')]
class StateMachineStateTranslationCollection extends EntityCollection
{
    /**
     * @return array<string>
     */
    public function getLanguageIds(): array
    {
        return $this->fmap(fn (StateMachineStateTranslationEntity $stateMachineStateTranslation) => $stateMachineStateTranslation->getLanguageId());
    }

    public function filterByLanguageId(string $id): self
    {
        return $this->filter(fn (StateMachineStateTranslationEntity $stateMachineStateTranslation) => $stateMachineStateTranslation->getLanguageId() === $id);
    }

    public function getApiAlias(): string
    {
        return 'state_machine_state_translation_collection';
    }

    protected function getExpectedClass(): string
    {
        return StateMachineStateEntity::class;
    }
}
