<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Country\Event;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Event\HeyFrameChannelEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('fundamentals@discovery')]
class CountryStateCriteriaEvent extends Event implements HeyFrameChannelEvent
{
    public function __construct(
        private readonly string $countryId,
        private readonly Request $request,
        private readonly Criteria $criteria,
        private readonly ChannelContext $channelContext
    ) {
    }

    public function getCountryId(): string
    {
        return $this->countryId;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getCriteria(): Criteria
    {
        return $this->criteria;
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
