<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Routing;

use HeyFrame\Core\ChannelRequest;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Routing\Event\MaintenanceModeRequestEvent;
use HeyFrame\Core\Framework\Util\Json;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
#[Package('framework')]
class MaintenanceModeResolver
{
    public function __construct(private readonly EventDispatcherInterface $eventDispatcher)
    {
    }

    public function shouldBeCached(Request $request): bool
    {
        return !$this->isActive($request) || !$this->isClientAllowed($request, self::getIps($request));
    }

    /**
     * @param array<string> $allowedIps
     */
    public function isClientAllowed(Request $request, array $allowedIps): bool
    {
        $isAllowed = IpUtils::checkIp((string) $request->getClientIp(), $allowedIps);

        $event = new MaintenanceModeRequestEvent($request, $allowedIps, $isAllowed);

        $this->eventDispatcher->dispatch($event);

        return $event->isClientAllowed();
    }

    public function isMaintenanceRequest(Request $request): bool
    {
        return $this->isActive($request) && !$this->isClientAllowed($request, self::getIps($request));
    }

    /**
     * @return string[]
     */
    private static function getIps(Request $request): array
    {
        $whitelist = $request->attributes->get(ChannelRequest::ATTRIBUTE_CHANNEL_MAINTENANCE_IP_WHITLELIST) ?? '';

        /** @var list<string> $allowedIps */
        $allowedIps = Json::decodeToList((string) $whitelist);

        return $allowedIps;
    }

    private function isActive(Request $request): bool
    {
        return (bool) $request->attributes->get(ChannelRequest::ATTRIBUTE_CHANNEL_MAINTENANCE);
    }
}
