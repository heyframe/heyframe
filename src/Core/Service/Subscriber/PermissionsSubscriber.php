<?php declare(strict_types=1);

namespace HeyFrame\Core\Service\Subscriber;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Service\Event\PermissionsGrantedEvent;
use HeyFrame\Core\Service\Event\PermissionsRevokedEvent;
use HeyFrame\Core\Service\LifecycleManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('framework')]
readonly class PermissionsSubscriber implements EventSubscriberInterface
{
    public function __construct(private LifecycleManager $manager)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PermissionsGrantedEvent::class => 'startServices',
            PermissionsRevokedEvent::class => 'stopServices',
        ];
    }

    public function startServices(PermissionsGrantedEvent $event): void
    {
        $this->manager->start($event->getContext());
    }

    public function stopServices(PermissionsRevokedEvent $event): void
    {
        $this->manager->stop($event->getContext());
    }
}
