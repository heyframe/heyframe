<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\ScheduledTask;

use HeyFrame\Core\Framework\App\Lifecycle\Update\AbstractAppUpdater;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @internal
 */
#[AsMessageHandler(handles: UpdateAppsTask::class)]
#[Package('framework')]
final class UpdateAppsHandler extends ScheduledTaskHandler
{
    /**
     * @internal
     */
    public function __construct(
        EntityRepository $scheduledTaskRepository,
        LoggerInterface $logger,
        private readonly AbstractAppUpdater $appUpdater
    ) {
        parent::__construct($scheduledTaskRepository, $logger);
    }

    public function run(): void
    {
        $this->appUpdater->updateApps(Context::createCLIContext());
    }
}
