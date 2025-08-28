<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Rule;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\CartException;
use HeyFrame\Core\Framework\Feature;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Rule\Exception\UnsupportedValueException;
use HeyFrame\Core\Framework\Rule\Rule;
use HeyFrame\Core\Framework\Rule\RuleComparison;
use HeyFrame\Core\Framework\Rule\RuleConfig;
use HeyFrame\Core\Framework\Rule\RuleConstraints;
use HeyFrame\Core\Framework\Rule\RuleScope;

/**
 * @final
 */
#[Package('fundamentals@after-sales')]
class CartVolumeRule extends Rule
{
    final public const RULE_NAME = 'cartVolume';

    /**
     * @internal
     */
    public function __construct(
        protected string $operator = self::OPERATOR_EQ,
        protected ?float $volume = null
    ) {
        parent::__construct();
    }

    public function match(RuleScope $scope): bool
    {
        if (!$scope instanceof CartRuleScope) {
            return false;
        }

        if ($this->volume === null) {
            if (!Feature::isActive('v6.8.0.0')) {
                // @phpstan-ignore-next-line
                throw new UnsupportedValueException(\gettype($this->volume), self::class);
            }
            throw CartException::unsupportedValue(\gettype($this->volume), self::class);
        }

        return RuleComparison::numeric($this->calculateCartVolume($scope->getCart()), $this->volume * self::VOLUME_FACTOR, $this->operator);
    }

    public function getConstraints(): array
    {
        return [
            'volume' => RuleConstraints::float(),
            'operator' => RuleConstraints::numericOperators(false),
        ];
    }

    public function getConfig(): RuleConfig
    {
        return (new RuleConfig())
            ->operatorSet(RuleConfig::OPERATOR_SET_NUMBER)
            ->numberField('volume', ['unit' => RuleConfig::UNIT_VOLUME]);
    }

    private function calculateCartVolume(Cart $cart): float
    {
        $volume = 0.0;

        foreach ($cart->getDeliveries() as $delivery) {
            $volume += $delivery->getPositions()->getVolume();
        }

        return $volume;
    }
}
