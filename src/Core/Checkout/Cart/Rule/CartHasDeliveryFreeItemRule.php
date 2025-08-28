<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Rule;

use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItemCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Rule\Rule;
use HeyFrame\Core\Framework\Rule\RuleConfig;
use HeyFrame\Core\Framework\Rule\RuleConstraints;
use HeyFrame\Core\Framework\Rule\RuleScope;

/**
 * @final
 */
#[Package('fundamentals@after-sales')]
class CartHasDeliveryFreeItemRule extends Rule
{
    final public const RULE_NAME = 'cartHasDeliveryFreeItem';

    /**
     * @internal
     */
    public function __construct(protected bool $allowed = true)
    {
        parent::__construct();
    }

    public function match(RuleScope $scope): bool
    {
        if ($scope instanceof LineItemScope) {
            return $this->isFreeDeliveryItem($scope->getLineItem()) === $this->allowed;
        }

        if (!$scope instanceof CartRuleScope) {
            return false;
        }

        $hasFreeDeliveryItems = $this->hasFreeDeliveryItems($scope->getCart()->getLineItems());

        return $hasFreeDeliveryItems === $this->allowed;
    }

    public function getConstraints(): array
    {
        return [
            'allowed' => RuleConstraints::bool(),
        ];
    }

    public function getConfig(): RuleConfig
    {
        return (new RuleConfig())
            ->booleanField('allowed');
    }

    private function hasFreeDeliveryItems(LineItemCollection $lineItems): bool
    {
        foreach ($lineItems->filterGoodsFlat() as $lineItem) {
            if ($this->isFreeDeliveryItem($lineItem) === true) {
                return true;
            }
        }

        return false;
    }

    private function isFreeDeliveryItem(LineItem $lineItem): bool
    {
        $deliveryInformation = $lineItem->getDeliveryInformation();
        if ($deliveryInformation === null) {
            return false;
        }

        return $deliveryInformation->getFreeDelivery();
    }
}
