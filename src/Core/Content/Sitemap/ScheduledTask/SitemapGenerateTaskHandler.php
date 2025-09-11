<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Sitemap\ScheduledTask;

use HeyFrame\Core\Content\Sitemap\Event\SitemapChannelCriteriaEvent;
use HeyFrame\Core\Content\Sitemap\Service\SitemapExporterInterface;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\NotEqualsFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskCollection;
use HeyFrame\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use HeyFrame\Core\System\Channel\Aggregate\ChannelDomain\ChannelDomainEntity;
use HeyFrame\Core\System\Channel\ChannelCollection;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
#[AsMessageHandler(handles: SitemapGenerateTask::class)]
#[Package('discovery')]
final class SitemapGenerateTaskHandler extends ScheduledTaskHandler
{
    /**
     * @internal
     *
     * @param EntityRepository<ScheduledTaskCollection> $scheduledTaskRepository
     * @param EntityRepository<ChannelCollection> $channelRepository
     */
    public function __construct(
        EntityRepository $scheduledTaskRepository,
        LoggerInterface $logger,
        private readonly EntityRepository $channelRepository,
        private readonly SystemConfigService $systemConfigService,
        private readonly MessageBusInterface $messageBus,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($scheduledTaskRepository, $logger);
    }

    public function run(): void
    {
        $sitemapRefreshStrategy = $this->systemConfigService->getInt('core.sitemap.sitemapRefreshStrategy');
        if ($sitemapRefreshStrategy !== SitemapExporterInterface::STRATEGY_SCHEDULED_TASK) {
            return;
        }

        $criteria = new Criteria();
        $criteria->addAssociation('domains');
        $criteria->addFilter(new NotEqualsFilter('domains.id', null));

        $criteria->addAssociation('type');
        $criteria->addFilter(new EqualsFilter('type.id', Defaults::SALES_CHANNEL_TYPE_STOREFRONT));

        $context = Context::createCLIContext();

        $this->eventDispatcher->dispatch(
            new SitemapChannelCriteriaEvent($criteria, $context)
        );

        $channels = $this->channelRepository->search($criteria, $context)->getEntities();

        foreach ($channels as $channel) {
            if ($channel->getDomains() === null) {
                continue;
            }

            $languageIds = $channel->getDomains()->map(fn (ChannelDomainEntity $channelDomain) => $channelDomain->getLanguageId());

            $languageIds = array_unique($languageIds);

            foreach ($languageIds as $languageId) {
                $this->messageBus->dispatch(new SitemapMessage($channel->getId(), $languageId, null, null, false));
            }
        }
    }
}
