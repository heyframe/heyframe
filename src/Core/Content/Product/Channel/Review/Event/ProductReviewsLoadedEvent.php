<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Review\Event;

use HeyFrame\Core\Content\Product\Channel\Review\ProductReviewResult;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\HeyFrameChannelEvent;
use HeyFrame\Core\Framework\Event\NestedEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

#[Package('after-sales')]
final class ProductReviewsLoadedEvent extends NestedEvent implements HeyFrameChannelEvent
{
    public function __construct(
        public ProductReviewResult $reviews,
        public Request $request,
        protected ChannelContext $channelContext,
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
