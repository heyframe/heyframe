<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Event;

use HeyFrame\Core\Checkout\Order\OrderEntity;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\HeyFrameChannelEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Allows the manipulation of the sales channel context after it was assembled from the order
 */
#[Package('checkout')]
class ChannelContextAssembledEvent extends Event implements HeyFrameChannelEvent
{
    /**
     * @internal
     */
    public function __construct(
        private readonly OrderEntity $order,
        private readonly ChannelContext $channelContext,
    ) {
    }

    public function getOrder(): OrderEntity
    {
        return $this->order;
    }

    public function getContext(): Context
    {
        return $this->channelContext->getContext();
    }

    public function getChannelContext(): ChannelContext
    {
        return $this->channelContext;
    }
}
