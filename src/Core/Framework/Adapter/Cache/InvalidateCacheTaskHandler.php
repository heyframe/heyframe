<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Adapter\Cache;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @internal
 */
#[AsMessageHandler(handles: InvalidateCacheTask::class)]
#[Package('framework')]
final class InvalidateCacheTaskHandler extends ScheduledTaskHandler
{
    public function __construct(
        EntityRepository $scheduledTaskRepository,
        LoggerInterface $logger,
        private readonly CacheInvalidator $cacheInvalidator
    ) {
        parent::__construct($scheduledTaskRepository, $logger);
    }

    public function run(): void
    {
        try {
            $this->cacheInvalidator->invalidateExpired();
        } catch (\Throwable) {
        }
    }
}
