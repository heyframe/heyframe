<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Api\EventListener\Authentication;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use HeyFrame\Core\Framework\Api\ApiException;
use HeyFrame\Core\Framework\Api\Util\AccessKeyHelper;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Routing\FrontApiRouteScope;
use HeyFrame\Core\Framework\Routing\KernelListenerPriorities;
use HeyFrame\Core\Framework\Routing\MaintenanceModeResolver;
use HeyFrame\Core\Framework\Routing\RouteScopeCheckTrait;
use HeyFrame\Core\Framework\Routing\RouteScopeRegistry;
use HeyFrame\Core\Framework\Util\Json;
use HeyFrame\Core\Framework\Util\UtilException;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\PlatformRequest;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @internal
 *
 * @codeCoverageIgnore Tested via an integration test
 *
 * @see \HeyFrame\Tests\Integration\Core\Framework\Api\EventListener\ChannelAuthenticationListenerTest
 */
#[Package('framework')]
class ChannelAuthenticationListener implements EventSubscriberInterface
{
    use RouteScopeCheckTrait;

    /**
     * @internal
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly RouteScopeRegistry $routeScopeRegistry,
        private readonly MaintenanceModeResolver $maintenanceModeResolver
    ) {
    }

    /**
     * @return array<string, string|array{0: string, 1: int}|list<array{0: string, 1?: int}>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => [
                'validateRequest',
                KernelListenerPriorities::KERNEL_CONTROLLER_EVENT_PRIORITY_AUTH_VALIDATE,
            ],
        ];
    }

    public function validateRequest(ControllerEvent $event): void
    {
        $request = $event->getRequest();

        if (!$request->attributes->get('auth_required', true)) {
            return;
        }

        if (!$this->isRequestScoped($request, FrontApiRouteScope::class)) {
            return;
        }

        $accessKey = $request->headers->get(PlatformRequest::HEADER_ACCESS_KEY);
        if (!$accessKey) {
            throw ApiException::unauthorized(
                'header',
                \sprintf('Header "%s" is required.', PlatformRequest::HEADER_ACCESS_KEY)
            );
        }

        $origin = AccessKeyHelper::getOrigin($accessKey);
        if ($origin !== 'channel') {
            throw ApiException::channelNotFound();
        }

        $channelData = $this->getChannelData($accessKey);

        $this->handleMaintenanceMode($request, $channelData);

        $request->attributes->set(PlatformRequest::ATTRIBUTE_CHANNEL_ID, $channelData['id']);
    }

    protected function getScopeRegistry(): RouteScopeRegistry
    {
        return $this->routeScopeRegistry;
    }

    /**
     * @return array<string, mixed>
     */
    private function getChannelData(string $accessKey): array
    {
        $builder = $this->connection->createQueryBuilder();

        $channelData = $builder->select(
            'channel.id AS id',
            'channel.maintenance AS maintenance',
            'channel.maintenance_ip_whitelist as maintenanceIpWhitelist'
        )
            ->from('channel')
            ->where('channel.access_key = :accessKey')
            ->andWhere('channel.active = :active')
            ->setParameter('accessKey', $accessKey)
            ->setParameter('active', true, Types::BOOLEAN)
            ->executeQuery()
            ->fetchAssociative();

        if (!\is_array($channelData)) {
            throw ApiException::channelNotFound();
        }

        $id = $channelData['id'] ?? null;

        if ($id === null || $id === '') {
            throw ApiException::channelNotFound();
        }

        $channelData['id'] = Uuid::fromBytesToHex($id);

        return $channelData;
    }

    /**
     * @param array<string, mixed> $channelData
     */
    private function handleMaintenanceMode(Request $request, array $channelData): void
    {
        $maintenance = (bool) ($channelData['maintenance'] ?? false);

        if (!$maintenance) {
            return;
        }

        if ($request->attributes->getBoolean(PlatformRequest::ATTRIBUTE_IS_ALLOWED_IN_MAINTENANCE)) {
            return;
        }

        try {
            /** @var string[] $allowedIps */
            $allowedIps = Json::decodeToList((string) ($channelData['maintenanceIpWhitelist'] ?? ''));
        } catch (UtilException) {
            return;
        }

        if ($this->maintenanceModeResolver->isClientAllowed($request, $allowedIps)) {
            return;
        }

        throw ApiException::channelInMaintenanceMode();
    }
}
