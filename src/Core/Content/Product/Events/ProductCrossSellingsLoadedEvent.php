<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Events;

use HeyFrame\Core\Content\Product\Channel\CrossSelling\CrossSellingElementCollection;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\HeyFrameChannelEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('inventory')]
class ProductCrossSellingsLoadedEvent extends Event implements HeyFrameChannelEvent
{
    public function __construct(
        private readonly CrossSellingElementCollection $result,
        private readonly ChannelContext $channelContext
    ) {
    }

    public function getContext(): Context
    {
        return $this->channelContext->getContext();
    }

    public function getCrossSellings(): CrossSellingElementCollection
    {
        return $this->result;
    }

    public function getChannelContext(): ChannelContext
    {
        return $this->channelContext;
    }
}
