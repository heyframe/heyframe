<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Page;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\HeyFrameChannelEvent;
use HeyFrame\Core\Framework\Event\NestedEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Struct;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

#[Package('framework')]
abstract class PageLoadedEvent extends NestedEvent implements HeyFrameChannelEvent
{
    public function __construct(
        protected ChannelContext $channelContext,
        protected Request $request
    ) {
    }

    /**
     * @return Page|Struct
     */
    abstract public function getPage();

    public function getChannelContext(): ChannelContext
    {
        return $this->channelContext;
    }

    public function getContext(): Context
    {
        return $this->channelContext->getContext();
    }

    public function getRequest(): Request
    {
        return $this->request;
    }
}
