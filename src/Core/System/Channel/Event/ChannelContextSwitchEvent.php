<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Event;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\HeyFrameChannelEvent;
use HeyFrame\Core\Framework\Event\NestedEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Validation\DataBag\DataBag;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('framework')]
class ChannelContextSwitchEvent extends NestedEvent implements HeyFrameChannelEvent
{
    public function __construct(
        private readonly ChannelContext $channelContext,
        private readonly DataBag $requestDataBag
    ) {
    }

    public function getContext(): Context
    {
        return $this->channelContext->getContext();
    }

    public function getRequestDataBag(): DataBag
    {
        return $this->requestDataBag;
    }

    public function getChannelContext(): ChannelContext
    {
        return $this->channelContext;
    }
}
