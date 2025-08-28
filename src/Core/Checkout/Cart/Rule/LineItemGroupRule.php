<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Rule;

use HeyFrame\Core\Checkout\Cart\CartException;
use HeyFrame\Core\Checkout\Cart\LineItem\Group\LineItemGroupBuilder;
use HeyFrame\Core\Checkout\Cart\LineItem\Group\LineItemGroupDefinition;
use HeyFrame\Core\Content\Rule\RuleCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Rule\Container\FilterRule;
use HeyFrame\Core\Framework\Rule\RuleConstraints;
use HeyFrame\Core\Framework\Rule\RuleScope;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @final
 */
#[Package('fundamentals@after-sales')]
class LineItemGroupRule extends FilterRule
{
    final public const RULE_NAME = 'cartLineItemInGroup';

    protected string $groupId;

    protected string $packagerKey;

    protected float $value;

    protected string $sorterKey;

    protected ?RuleCollection $rules = null;

    /**
     * @throws CartException
     */
    public function match(RuleScope $scope): bool
    {
        if (!$scope instanceof CartRuleScope) {
            return false;
        }

        $groupDefinition = new LineItemGroupDefinition(
            $this->groupId,
            $this->packagerKey,
            $this->value,
            $this->sorterKey,
            $this->rules ?? new RuleCollection()
        );

        $builder = $scope->getCart()->getData()->get(LineItemGroupBuilder::class);
        if (!$builder instanceof LineItemGroupBuilder) {
            return false;
        }

        $results = $builder->findGroupPackages(
            [$groupDefinition],
            $scope->getCart(),
            $scope->getChannelContext()
        );

        return $results->hasFoundItems();
    }

    public function getConstraints(): array
    {
        return [
            'groupId' => RuleConstraints::string(),
            'packagerKey' => RuleConstraints::string(),
            'value' => RuleConstraints::float(),
            'sorterKey' => RuleConstraints::string(),
            'rules' => [new NotBlank(), new Type('container')],
        ];
    }
}
