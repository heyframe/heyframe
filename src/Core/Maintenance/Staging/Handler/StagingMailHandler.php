<?php declare(strict_types=1);

namespace HeyFrame\Core\Maintenance\Staging\Handler;

use HeyFrame\Core\Content\Mail\Service\MailSender;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Maintenance\Staging\Event\SetupStagingEvent;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;

/**
 * @internal
 */
#[Package('framework')]
readonly class StagingMailHandler
{
    public function __construct(
        private SystemConfigService $systemConfigService
    ) {
    }

    public function __invoke(SetupStagingEvent $event): void
    {
        if (!$event->disableMailDelivery) {
            return;
        }

        $this->systemConfigService->set(MailSender::DISABLE_MAIL_DELIVERY, true);

        $event->io->info('Disabled mail delivery.');
    }
}
