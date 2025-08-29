<?php declare(strict_types=1);

namespace HeyFrame\Core\Service\ServiceRegistry;

use HeyFrame\Core\Framework\App\InstanceId\InstanceIdProvider;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Service\Message\LogPermissionToRegistryMessage;
use HeyFrame\Core\Service\Permission\ConsentState;
use HeyFrame\Core\Service\Permission\PermissionsConsent;
use HeyFrame\Core\Service\Permission\RemoteLogger;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @internal
 */
#[Package('framework')]
class PermissionLogger implements RemoteLogger
{
    public const CONFIG_STORE_LICENSE_HOST = 'core.store.licenseHost';

    public function __construct(
        private readonly Client $client,
        private readonly MessageBusInterface $messageBus,
        private readonly InstanceIdProvider $instanceIdProvider,
        private readonly SystemConfigService $systemConfigService,
    ) {
    }

    public function log(PermissionsConsent $consent, ConsentState $state): void
    {
        $this->messageBus->dispatch(new LogPermissionToRegistryMessage($consent, $state));
    }

    public function logSync(PermissionsConsent $consent, ConsentState $state): void
    {
        if ($state === ConsentState::GRANTED) {
            $this->client->saveConsent(
                new SaveConsentRequest(
                    identifier: $consent->identifier,
                    consentingUserId: $consent->consentingUserId,
                    instanceIdentifier: $this->instanceIdProvider->getInstanceId(),
                    consentDate: $consent->grantedAt->format(\DateTime::ATOM),
                    consentRevision: $consent->revision,
                    licenseHost: $this->systemConfigService->getString(self::CONFIG_STORE_LICENSE_HOST),
                )
            );
        }

        if ($state === ConsentState::REVOKED) {
            $this->client->revokeConsent($consent->identifier);
        }
    }
}
