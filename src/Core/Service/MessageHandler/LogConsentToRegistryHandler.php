<?php declare(strict_types=1);

namespace HeyFrame\Core\Service\MessageHandler;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Service\Message\LogPermissionToRegistryMessage;
use HeyFrame\Core\Service\ServiceRegistry\PermissionLogger;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @internal
 */
#[Package('framework')]
#[AsMessageHandler]
final class LogConsentToRegistryHandler
{
    public function __construct(private readonly PermissionLogger $logger)
    {
    }

    public function __invoke(LogPermissionToRegistryMessage $message): void
    {
        $this->logger->logSync($message->permissionsConsent, $message->consentState);
    }
}
