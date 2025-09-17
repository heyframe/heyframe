<?php

declare(strict_types=1);

namespace HeyFrame\Core\Content\Cookie\Event;

use HeyFrame\Core\Content\Cookie\Struct\CookieGroupCollection;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\HeyFrameChannelEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('framework')]
class CookieGroupCollectEvent implements HeyFrameChannelEvent
{
    public function __construct(
        public CookieGroupCollection $cookieGroupCollection,
        public ChannelContext $channelContext,
    ) {
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
