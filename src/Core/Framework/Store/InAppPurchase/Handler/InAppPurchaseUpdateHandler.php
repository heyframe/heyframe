<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Store\InAppPurchase\Handler;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use HeyFrame\Core\Framework\Store\InAppPurchase\InAppPurchaseUpdateTask;
use HeyFrame\Core\Framework\Store\InAppPurchase\Services\InAppPurchaseUpdater;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
#[AsMessageHandler(handles: InAppPurchaseUpdateTask::class)]
#[Package('checkout')]
final class InAppPurchaseUpdateHandler extends ScheduledTaskHandler
{
    public function __construct(
        EntityRepository $scheduledTaskRepository,
        LoggerInterface $logger,
        private readonly InAppPurchaseUpdater $iapUpdater
    ) {
        parent::__construct($scheduledTaskRepository, $logger);
    }

    public function run(): void
    {
        $context = Context::createCLIContext();
        $this->iapUpdater->update($context);
    }
}
