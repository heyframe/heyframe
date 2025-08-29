<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\InstanceId;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Framework\App\AppException;
use HeyFrame\Core\Framework\App\Exception\InstanceIdChangeSuggestedException;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Util\Random;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 *
 * @phpstan-import-type InstanceIdV1Config from InstanceId
 * @phpstan-import-type InstanceIdV2Config from InstanceId
 */
#[Package('framework')]
class InstanceIdProvider
{
    final public const SHOP_ID_SYSTEM_CONFIG_KEY = 'core.app.instanceId';
    final public const SHOP_ID_SYSTEM_CONFIG_KEY_V2 = 'core.app.instanceIdV2';

    public function __construct(
        private readonly SystemConfigService $systemConfigService,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly Connection $connection,
        private readonly FingerprintGenerator $fingerprintGenerator
    ) {
    }

    /**
     * @throws InstanceIdChangeSuggestedException
     */
    public function getInstanceId(): string
    {
        $instanceId = $this->fetchInstanceIdFromSystemConfig() ?? $this->regenerateAndSetInstanceId();

        $fingerprintsComparison = $this->fingerprintGenerator->matchFingerprints($instanceId->fingerprints);
        if (!$fingerprintsComparison->isMatching()) {
            if ($this->hasAppsRegisteredAtAppServers()) {
                throw AppException::instanceIdChangeSuggested($instanceId, $fingerprintsComparison);
            }

            // if the shop does not have any apps we can update the existing shop id value
            // with the new APP_URL as no app knows the shop id
            $this->regenerateAndSetInstanceId($instanceId->id);
        }

        return $instanceId->id;
    }

    public function regenerateAndSetInstanceId(?string $existingInstanceId = null): InstanceId
    {
        $instanceId = InstanceId::v2(
            $existingInstanceId ?? Random::getAlphanumericString(16),
            $this->fingerprintGenerator->takeFingerprints(),
        );

        $this->setInstanceId($instanceId);

        return $instanceId;
    }

    public function deleteInstanceId(): void
    {
        $this->systemConfigService->delete(self::SHOP_ID_SYSTEM_CONFIG_KEY);
        $this->systemConfigService->delete(self::SHOP_ID_SYSTEM_CONFIG_KEY_V2);

        $this->eventDispatcher->dispatch(new InstanceIdDeletedEvent());
    }

    private function setInstanceId(InstanceId $instanceId): void
    {
        $oldInstanceId = $this->systemConfigService->get(self::SHOP_ID_SYSTEM_CONFIG_KEY_V2)
            ?? $this->systemConfigService->get(self::SHOP_ID_SYSTEM_CONFIG_KEY);
        if (\is_array($oldInstanceId)) {
            $oldInstanceId = InstanceId::fromSystemConfig($oldInstanceId);
        } else {
            $oldInstanceId = null;
        }

        $this->systemConfigService->set(self::SHOP_ID_SYSTEM_CONFIG_KEY_V2, (array) $instanceId);
        $this->eventDispatcher->dispatch(new InstanceIdChangedEvent($instanceId, $oldInstanceId));
    }

    private function hasAppsRegisteredAtAppServers(): bool
    {
        return (int) $this->connection->fetchOne('SELECT COUNT(id) FROM app WHERE app_secret IS NOT NULL') > 0;
    }

    private function fetchInstanceIdFromSystemConfig(): ?InstanceId
    {
        /** @var InstanceIdV2Config|null $instanceIdV2 */
        $instanceIdV2 = $this->systemConfigService->get(self::SHOP_ID_SYSTEM_CONFIG_KEY_V2);
        if (\is_array($instanceIdV2)) {
            return InstanceId::fromSystemConfig($instanceIdV2);
        }

        /** @var InstanceIdV1Config|null $instanceIdV1 */
        $instanceIdV1 = $this->systemConfigService->get(self::SHOP_ID_SYSTEM_CONFIG_KEY);
        if (\is_array($instanceIdV1)) {
            $instanceIdV1 = InstanceId::fromSystemConfig($instanceIdV1);

            return $this->regenerateAndSetInstanceId($instanceIdV1->id);
        }

        return null;
    }
}
