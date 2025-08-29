<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Gateway\Command\Struct;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Payment\PaymentMethodCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Struct;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
class CheckoutGatewayPayloadStruct extends Struct
{
    /**
     * @internal
     */
    public function __construct(
        protected Cart $cart,
        protected ChannelContext $channelContext,
        protected PaymentMethodCollection $paymentMethods,
    ) {
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    public function getChannelContext(): ChannelContext
    {
        return $this->channelContext;
    }

    public function getPaymentMethods(): PaymentMethodCollection
    {
        return $this->paymentMethods;
    }
}
