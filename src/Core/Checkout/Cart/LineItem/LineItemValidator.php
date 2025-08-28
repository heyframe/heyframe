<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\LineItem;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\CartValidatorInterface;
use HeyFrame\Core\Checkout\Cart\Error\ErrorCollection;
use HeyFrame\Core\Checkout\Cart\Error\IncompleteLineItemError;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
class LineItemValidator implements CartValidatorInterface
{
    public function validate(Cart $cart, ErrorCollection $errors, ChannelContext $context): void
    {
        foreach ($cart->getLineItems()->getFlat() as $lineItem) {
            if ($lineItem->getLabel() === null && $lineItem->getType() !== LineItem::CONTAINER_LINE_ITEM) {
                $errors->add(new IncompleteLineItemError($lineItem->getId(), 'label'));
                $cart->getLineItems()->removeElement($lineItem);
            }

            if ($lineItem->getPrice() === null) {
                $errors->add(new IncompleteLineItemError($lineItem->getId(), 'price'));
                $cart->getLineItems()->removeElement($lineItem);
            }
        }
    }
}
