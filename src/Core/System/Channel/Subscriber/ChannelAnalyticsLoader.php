<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Subscriber;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\Aggregate\ChannelAnalytics\ChannelAnalyticsCollection;
use HeyFrame\Storefront\Event\StorefrontRenderEvent;

/**
 * @internal
 */
#[Package('discovery')]
class ChannelAnalyticsLoader
{
    /**
     * @param EntityRepository<ChannelAnalyticsCollection> $channelAnalyticsRepository
     */
    public function __construct(
        private readonly EntityRepository $channelAnalyticsRepository,
    ) {
    }

    public function loadAnalytics(StorefrontRenderEvent $event): void
    {
        $channelContext = $event->getChannelContext();
        $channel = $channelContext->getChannel();
        $analyticsId = $channel->getAnalyticsId();

        if (empty($analyticsId)) {
            return;
        }

        $criteria = new Criteria([$analyticsId]);
        $criteria->setTitle('sales-channel::load-analytics');

        $analytics = $this->channelAnalyticsRepository->search($criteria, $channelContext->getContext())->getEntities()->first();

        $event->setParameter('storefrontAnalytics', $analytics);
    }
}
