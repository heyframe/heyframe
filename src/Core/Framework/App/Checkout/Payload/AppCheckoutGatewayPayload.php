<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Checkout\Payload;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Framework\App\Payload\Source;
use HeyFrame\Core\Framework\App\Payload\SourcedPayloadInterface;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\CloneTrait;
use HeyFrame\Core\Framework\Struct\JsonSerializableTrait;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * @internal only for use by the app-system
 */
#[Package('checkout')]
class AppCheckoutGatewayPayload implements SourcedPayloadInterface
{
    use CloneTrait;
    use JsonSerializableTrait;

    protected Source $source;

    /**
     * @param string[] $paymentMethods
     * @param string[] $shippingMethods
     *
     * @internal
     */
    public function __construct(
        protected ChannelContext $channelContext,
        protected Cart $cart,
        protected array $paymentMethods = [],
        protected array $shippingMethods = []
    ) {
    }

    public function setSource(Source $source): void
    {
        $this->source = $source;
    }

    public function getSource(): Source
    {
        return $this->source;
    }

    public function getChannelContext(): ChannelContext
    {
        return $this->channelContext;
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    /**
     * @return string[]
     */
    public function getPaymentMethods(): array
    {
        return $this->paymentMethods;
    }

    /**
     * @return string[]
     */
    public function getShippingMethods(): array
    {
        return $this->shippingMethods;
    }
}
