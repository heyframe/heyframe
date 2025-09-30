<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Event\RouteRequest;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Event\HeyFrameChannelEvent;
use HeyFrame\Core\Framework\Event\NestedEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

#[Package('framework')]
abstract class RouteRequestEvent extends NestedEvent implements HeyFrameChannelEvent
{
    private readonly Criteria $criteria;

    public function __construct(
        private readonly Request $storefrontRequest,
        private readonly Request $storeApiRequest,
        private readonly ChannelContext $channelContext,
        ?Criteria $criteria = null
    ) {
        $this->criteria = $criteria ?? new Criteria();
    }

    public function getFrontendRequest(): Request
    {
        return $this->storefrontRequest;
    }

    public function getStoreApiRequest(): Request
    {
        return $this->storeApiRequest;
    }

    public function getChannelContext(): ChannelContext
    {
        return $this->channelContext;
    }

    public function getContext(): Context
    {
        return $this->channelContext->getContext();
    }

    public function getCriteria(): Criteria
    {
        return $this->criteria;
    }
}
