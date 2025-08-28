<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Currency;

use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use HeyFrame\Core\Framework\DataAbstractionLayer\PartialEntity;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('fundamentals@framework')]
class CurrencyLoadSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            CurrencyEvents::CURRENCY_LOADED_EVENT => 'setDefault',
            'currency.partial_loaded' => 'setDefault',
        ];
    }

    /**
     * @param EntityLoadedEvent<CurrencyEntity|PartialEntity> $event
     */
    public function setDefault(EntityLoadedEvent $event): void
    {
        foreach ($event->getEntities() as $entity) {
            $entity->assign([
                'isSystemDefault' => ($entity->get('id') === Defaults::CURRENCY),
            ]);
        }
    }
}
