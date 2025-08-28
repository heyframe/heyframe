<?php declare(strict_types=1);

namespace HeyFrame\Core\Service\Subscriber;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Service\Event\NewServicesInstalledEvent;
use HeyFrame\Core\Service\Event\ServiceInstalledEvent;
use HeyFrame\Core\Service\Event\ServiceUpdatedEvent;
use HeyFrame\Core\Service\LifecycleManager;
use HeyFrame\Core\Service\Notification;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('framework')]
readonly class ServiceLifecycleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LifecycleManager $lifecycleManager,
        private Notification $notification,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ServiceInstalledEvent::class => 'syncState',
            ServiceUpdatedEvent::class => 'syncState',
            NewServicesInstalledEvent::class => 'sendInstalledNotification',
        ];
    }

    public function syncState(ServiceInstalledEvent|ServiceUpdatedEvent $event): void
    {
        $this->lifecycleManager->syncState($event->service, $event->getContext());
    }

    public function sendInstalledNotification(NewServicesInstalledEvent $event): void
    {
        $this->notification->newServicesInstalled();
    }
}
