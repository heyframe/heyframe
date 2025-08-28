<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Delivery;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\CartValidatorInterface;
use HeyFrame\Core\Checkout\Cart\Error\ErrorCollection;
use HeyFrame\Core\Checkout\Shipping\Cart\Error\ShippingMethodBlockedError;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
class DeliveryValidator implements CartValidatorInterface
{
    public function validate(Cart $cart, ErrorCollection $errors, ChannelContext $context): void
    {
        foreach ($cart->getDeliveries() as $delivery) {
            $shippingMethod = $delivery->getShippingMethod();
            $ruleId = $shippingMethod->getAvailabilityRuleId();

            $matches = \in_array($ruleId, $context->getRuleIds(), true) || $ruleId === null;

            if ($matches && $shippingMethod->getActive()) {
                continue;
            }

            $errors->add(
                new ShippingMethodBlockedError(
                    (string) $shippingMethod->getTranslation('name')
                )
            );
        }
    }
}
