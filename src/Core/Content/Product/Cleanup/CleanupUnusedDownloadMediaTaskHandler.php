<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Cleanup;

use HeyFrame\Core\Content\Media\UnusedMediaPurger;
use HeyFrame\Core\Content\Product\Aggregate\ProductDownload\ProductDownloadDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskCollection;
use HeyFrame\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @internal
 */
#[Package('inventory')]
#[AsMessageHandler(handles: CleanupUnusedDownloadMediaTask::class)]
final class CleanupUnusedDownloadMediaTaskHandler extends ScheduledTaskHandler
{
    /**
     * @param EntityRepository<ScheduledTaskCollection> $repository
     */
    public function __construct(
        EntityRepository $repository,
        LoggerInterface $logger,
        private readonly UnusedMediaPurger $unusedMediaPurger
    ) {
        parent::__construct($repository, $logger);
    }

    public function run(): void
    {
        $this->unusedMediaPurger->deleteNotUsedMedia(
            null,
            null,
            null,
            ProductDownloadDefinition::ENTITY_NAME
        );
    }
}
