<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Sitemap\ScheduledTask;

use Psr\Log\LoggerInterface;
use HeyFrame\Core\Content\Sitemap\Exception\AlreadyLockedException;
use HeyFrame\Core\Content\Sitemap\Service\SitemapExporterInterface;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\Context\AbstractChannelContextFactory;
use HeyFrame\Core\System\Channel\Context\ChannelContextService;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @internal
 */
#[AsMessageHandler]
#[Package('discovery')]
final readonly class SitemapMessageHandler
{
    /**
     * @internal
     */
    public function __construct(
        private AbstractChannelContextFactory $channelContextFactory,
        private SitemapExporterInterface $sitemapExporter,
        private LoggerInterface $logger,
        private SystemConfigService $systemConfigService,
    ) {
    }

    public function __invoke(SitemapMessage $message): void
    {
        $sitemapRefreshStrategy = $this->systemConfigService->getInt('core.sitemap.sitemapRefreshStrategy');
        if ($sitemapRefreshStrategy !== SitemapExporterInterface::STRATEGY_SCHEDULED_TASK) {
            return;
        }

        $this->generate($message);
    }

    private function generate(SitemapMessage $message): void
    {
        if ($message->getLastChannelId() === null || $message->getLastLanguageId() === null) {
            return;
        }

        $channelContext = $this->channelContextFactory->create('', $message->getLastChannelId(), [ChannelContextService::LANGUAGE_ID => $message->getLastLanguageId()]);

        try {
            $this->sitemapExporter->generate($channelContext, true, $message->getLastProvider(), $message->getNextOffset());
        } catch (AlreadyLockedException $exception) {
            $this->logger->error(\sprintf('ERROR: %s', $exception->getMessage()));
        }
    }
}
