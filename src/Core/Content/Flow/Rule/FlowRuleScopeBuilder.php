<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Flow\Rule;

use HeyFrame\Core\Checkout\Cart\CartBehavior;
use HeyFrame\Core\Checkout\Cart\CartDataCollectorInterface;
use HeyFrame\Core\Checkout\Cart\Order\OrderConverter;
use HeyFrame\Core\Checkout\Order\OrderEntity;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @internal
 */
#[Package('after-sales')]
class FlowRuleScopeBuilder implements ResetInterface
{
    /**
     * @var array<string, FlowRuleScope>
     */
    private array $scopes = [];

    /**
     * @param iterable<CartDataCollectorInterface> $collectors
     */
    public function __construct(
        private readonly OrderConverter $orderConverter,
        private readonly iterable $collectors
    ) {
    }

    public function reset(): void
    {
        $this->scopes = [];
    }

    public function build(OrderEntity $order, Context $context): FlowRuleScope
    {
        if (\array_key_exists($order->getId(), $this->scopes)) {
            return $this->scopes[$order->getId()];
        }

        $context = $this->orderConverter->assembleChannelContext($order, $context);
        $cart = $this->orderConverter->convertToCart($order, $context->getContext());
        $behavior = new CartBehavior($context->getPermissions());

        foreach ($this->collectors as $collector) {
            $collector->collect($cart->getData(), $cart, $context, $behavior);
        }

        return $this->scopes[$order->getId()] = new FlowRuleScope($order, $cart, $context);
    }
}
