<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Pagelet;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\NestedEvent;
use HeyFrame\Core\Framework\Event\HeyFrameChannelEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Frontend\Pagelet\Pagelet;
use Symfony\Component\HttpFoundation\Request;

#[Package('framework')]
abstract class PageletLoadedEvent extends NestedEvent implements HeyFrameChannelEvent
{
    public function __construct(
        protected ChannelContext $channelContext,
        protected Request $request,
    ) {
    }

    /**
     * @return Pagelet
     */
    abstract public function getPagelet();

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
