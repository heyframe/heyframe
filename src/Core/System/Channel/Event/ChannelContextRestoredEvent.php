<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Event;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\NestedEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('framework')]
class ChannelContextRestoredEvent extends NestedEvent
{
    public function __construct(
        private readonly ChannelContext $restoredContext,
        private readonly ChannelContext $currentContext
    ) {
    }

    public function getRestoredChannelContext(): ChannelContext
    {
        return $this->restoredContext;
    }

    public function getContext(): Context
    {
        return $this->restoredContext->getContext();
    }

    public function getCurrentChannelContext(): ChannelContext
    {
        return $this->currentContext;
    }
}
