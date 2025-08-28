<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Payment\Cart;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\CartValidatorInterface;
use HeyFrame\Core\Checkout\Cart\Error\ErrorCollection;
use HeyFrame\Core\Checkout\Payment\Cart\Error\PaymentMethodBlockedError;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
class PaymentMethodValidator implements CartValidatorInterface
{
    public function validate(Cart $cart, ErrorCollection $errors, ChannelContext $context): void
    {
        $paymentMethod = $context->getPaymentMethod();
        if (!$paymentMethod->getActive()) {
            $errors->add(
                new PaymentMethodBlockedError((string) $paymentMethod->getTranslation('name'), 'inactive')
            );
        }

        $ruleId = $paymentMethod->getAvailabilityRuleId();

        if ($ruleId && !\in_array($ruleId, $context->getRuleIds(), true)) {
            $errors->add(
                new PaymentMethodBlockedError((string) $paymentMethod->getTranslation('name'), 'rule not matching')
            );
        }

        if (!\in_array($paymentMethod->getId(), $context->getChannel()->getPaymentMethodIds() ?? [], true)) {
            $errors->add(
                new PaymentMethodBlockedError((string) $paymentMethod->getTranslation('name'), 'not allowed')
            );
        }
    }
}
