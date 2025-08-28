<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Rule\Aggregate\RuleCondition;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<RuleConditionEntity>
 */
class RuleConditionCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'rule_condition_collection';
    }

    protected function getExpectedClass(): string
    {
        return RuleConditionEntity::class;
    }
}
