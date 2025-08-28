<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\TaxProvider\Payload;

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
class TaxProviderPayload implements SourcedPayloadInterface
{
    use CloneTrait;
    use JsonSerializableTrait;

    private Source $source;

    public function __construct(
        private readonly Cart $cart,
        private readonly ChannelContext $context
    ) {
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    public function getContext(): ChannelContext
    {
        return $this->context;
    }

    public function setSource(Source $source): void
    {
        $this->source = $source;
    }
}
