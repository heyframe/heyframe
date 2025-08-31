<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Transaction;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use HeyFrame\Core\Checkout\Cart\Transaction\Struct\Transaction;
use HeyFrame\Core\Checkout\Cart\Transaction\Struct\TransactionCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
class TransactionProcessor
{
    public function process(Cart $cart, ChannelContext $context): TransactionCollection
    {
        $price = $cart->getPrice()->getTotalPrice();

        return new TransactionCollection([
            new Transaction(
                new CalculatedPrice(
                    $price,
                    $price,
                ),
                $context->getPaymentMethod()->getId()
            ),
        ]);
    }
}
