<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Payment\Payload\Struct;

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
class ValidatePayload implements SourcedPayloadInterface
{
    use CloneTrait;
    use JsonSerializableTrait;
    use RemoveAppTrait;

    protected Source $source;

    /**
     * @param mixed[] $requestData
     */
    public function __construct(
        protected Cart $cart,
        protected array $requestData,
        protected ChannelContext $channelContext
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

    public function getCart(): Cart
    {
        return $this->cart;
    }

    /**
     * @return mixed[]
     */
    public function getRequestData(): array
    {
        return $this->requestData;
    }

    public function getChannelContext(): ChannelContext
    {
        return $this->channelContext;
    }
}
