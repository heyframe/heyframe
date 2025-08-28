<?php declare(strict_types=1);

namespace HeyFrame\Core\Service\Subscriber;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Service\Event\ServiceOutdatedEvent;
use HeyFrame\Core\Service\ServiceLifecycle;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('framework')]
class ServiceOutdatedSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly ServiceLifecycle $serviceLifecycle)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ServiceOutdatedEvent::class => 'updateService',
        ];
    }

    public function updateService(ServiceOutdatedEvent $event): void
    {
        $this->serviceLifecycle->update($event->serviceName, $event->getContext());
    }
}
