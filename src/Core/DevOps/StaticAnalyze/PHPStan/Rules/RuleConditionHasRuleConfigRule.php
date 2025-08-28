<?php declare(strict_types=1);

namespace HeyFrame\Core\DevOps\StaticAnalyze\PHPStan\Rules;

use HeyFrame\Core\Checkout\Cart\Rule\AlwaysValidRule;
use HeyFrame\Core\Checkout\Cart\Rule\GoodsCountRule;
use HeyFrame\Core\Checkout\Cart\Rule\GoodsPriceRule;
use HeyFrame\Core\Checkout\Cart\Rule\LineItemCustomFieldRule;
use HeyFrame\Core\Checkout\Cart\Rule\LineItemGoodsTotalRule;
use HeyFrame\Core\Checkout\Cart\Rule\LineItemGroupRule;
use HeyFrame\Core\Checkout\Cart\Rule\LineItemInCategoryRule;
use HeyFrame\Core\Checkout\Cart\Rule\LineItemPropertyRule;
use HeyFrame\Core\Checkout\Cart\Rule\LineItemPurchasePriceRule;
use HeyFrame\Core\Checkout\Cart\Rule\LineItemRule;
use HeyFrame\Core\Checkout\Cart\Rule\LineItemWithQuantityRule;
use HeyFrame\Core\Checkout\Cart\Rule\LineItemWrapperRule;
use HeyFrame\Core\Checkout\Customer\Rule\BillingZipCodeRule;
use HeyFrame\Core\Checkout\Customer\Rule\CustomerCustomFieldRule;
use HeyFrame\Core\Checkout\Customer\Rule\ShippingZipCodeRule;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Rule\Container\AndRule;
use HeyFrame\Core\Framework\Rule\Container\Container;
use HeyFrame\Core\Framework\Rule\Container\FilterRule;
use HeyFrame\Core\Framework\Rule\Container\MatchAllLineItemsRule;
use HeyFrame\Core\Framework\Rule\Container\NotRule;
use HeyFrame\Core\Framework\Rule\Container\OrRule;
use HeyFrame\Core\Framework\Rule\Container\XorRule;
use HeyFrame\Core\Framework\Rule\Container\ZipCodeRule;
use HeyFrame\Core\Framework\Rule\DateRangeRule;
use HeyFrame\Core\Framework\Rule\Rule as HeyFrameRule;
use HeyFrame\Core\Framework\Rule\ScriptRule;
use HeyFrame\Core\Framework\Rule\SimpleRule;
use HeyFrame\Core\Framework\Rule\TimeRangeRule;
use HeyFrame\Core\Test\Stub\Rule\FalseRule;
use HeyFrame\Core\Test\Stub\Rule\TrueRule;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<InClassNode>
 *
 * @internal
 */
#[Package('framework')]
class RuleConditionHasRuleConfigRule implements Rule
{
    /**
     * @var list<string>
     */
    private array $rulesAllowedToBeWithoutConfig = [
        ZipCodeRule::class,
        FilterRule::class,
        Container::class,
        AndRule::class,
        NotRule::class,
        OrRule::class,
        XorRule::class,
        MatchAllLineItemsRule::class,
        ScriptRule::class,
        DateRangeRule::class,
        SimpleRule::class,
        TimeRangeRule::class,
        GoodsCountRule::class,
        GoodsPriceRule::class,
        LineItemRule::class,
        LineItemWithQuantityRule::class,
        LineItemWrapperRule::class,
        BillingZipCodeRule::class,
        ShippingZipCodeRule::class,
        AlwaysValidRule::class,
        LineItemPropertyRule::class,
        LineItemPurchasePriceRule::class,
        LineItemInCategoryRule::class,
        LineItemCustomFieldRule::class,
        LineItemGoodsTotalRule::class,
        CustomerCustomFieldRule::class,
        LineItemGroupRule::class,
        FalseRule::class,
        TrueRule::class,
    ];

    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    /**
     * @param InClassNode $node
     *
     * @return array<array-key, RuleError|string>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$this->isRuleClass($scope) || $this->isAllowed($scope) || $this->isValid($scope)) {
            if ($this->isAllowed($scope) && $this->isValid($scope)) {
                return [
                    RuleErrorBuilder::message('This class is implementing the getConfig function and has a own admin component. Remove getConfig or the component.')
                        ->identifier('heyframe.ruleConfig')
                        ->build(),
                ];
            }

            return [];
        }

        return [
            RuleErrorBuilder::message('This class has to implement getConfig or implement a new admin component.')
                ->identifier('heyframe.ruleConfig')
                ->build(),
        ];
    }

    private function isValid(Scope $scope): bool
    {
        $class = $scope->getClassReflection();
        if ($class === null || !$class->hasMethod('getConfig')) {
            return false;
        }

        $declaringClass = $class->getMethod('getConfig', $scope)->getDeclaringClass();

        return $declaringClass->getName() !== HeyFrameRule::class;
    }

    private function isAllowed(Scope $scope): bool
    {
        $class = $scope->getClassReflection();
        if ($class === null) {
            return false;
        }

        return \in_array($class->getName(), $this->rulesAllowedToBeWithoutConfig, true);
    }

    private function isRuleClass(Scope $scope): bool
    {
        $class = $scope->getClassReflection();
        if ($class === null) {
            return false;
        }

        $namespace = $class->getName();
        if (!\str_contains($namespace, 'HeyFrame\\Tests\\Unit\\') && !\str_contains($namespace, 'HeyFrame\\Tests\\Migration\\')) {
            return false;
        }

        return $class->is(HeyFrameRule::class);
    }
}
