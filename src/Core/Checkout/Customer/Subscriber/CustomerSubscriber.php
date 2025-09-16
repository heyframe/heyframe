<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Subscriber;

use HeyFrame\Core\Checkout\Customer\CustomerEvents;
use HeyFrame\Core\Content\Product\ProductEntity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use HeyFrame\Core\Framework\DataAbstractionLayer\PartialEntity;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
/**
 * @internal
 */
#[Package('checkout')]
class CustomerSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            CustomerEvents::CUSTOMER_LOADED_EVENT => 'loaded',
        ];
    }

    /**
     * @param EntityLoadedEvent<ProductEntity|PartialEntity> $event
     */
    public function loaded(EntityLoadedEvent $event): void
    {
        foreach ($event->getEntities() as $entity) {

        }
    }
}
