<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Navigation\Event;

use HeyFrame\Core\Content\Navigation\Tree\Tree;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\HeyFrameChannelEvent;
use HeyFrame\Core\Framework\Event\NestedEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('discovery')]
class NavigationLoadedEvent extends NestedEvent implements HeyFrameChannelEvent
{
    public function __construct(
        protected Tree $navigation,
        protected ChannelContext $channelContext,
    ) {
    }

    public function getContext(): Context
    {
        return $this->channelContext->getContext();
    }

    public function getNavigation(): Tree
    {
        return $this->navigation;
    }

    public function getChannelContext(): ChannelContext
    {
        return $this->channelContext;
    }
}
