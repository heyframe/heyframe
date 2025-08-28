<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Event;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\HeyFrameChannelEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('framework')]
class ChannelContextTokenChangeEvent extends Event implements HeyFrameChannelEvent
{
    public function __construct(
        protected ChannelContext $channelContext,
        protected string $previousToken,
        protected string $currentToken
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

    public function getPreviousToken(): string
    {
        return $this->previousToken;
    }

    public function getCurrentToken(): string
    {
        return $this->currentToken;
    }
}
