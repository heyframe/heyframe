<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Routing\Event;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\HeyFrameChannelEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('framework')]
class ChannelContextResolvedEvent extends Event implements HeyFrameChannelEvent
{
    public function __construct(
        private readonly ChannelContext $channelContext,
        private readonly string $usedToken
    ) {
    }

    public function getChannelContext(): ChannelContext
    {
        return $this->channelContext;
    }

    public function getContext(): Context
    {
        return $this->channelContext->getContext();
    }

    public function getUsedToken(): string
    {
        return $this->usedToken;
    }
}
