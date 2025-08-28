<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Subscriber;

use HeyFrame\Core\Framework\App\AppEntity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal only for use by the app-system
 */
#[Package('framework')]
class AppLoadedSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'app.loaded' => 'unserialize',
        ];
    }

    /**
     * @param EntityLoadedEvent<AppEntity> $event
     */
    public function unserialize(EntityLoadedEvent $event): void
    {
        foreach ($event->getEntities() as $app) {
            $iconRaw = $app->getIconRaw();
            if ($iconRaw !== null) {
                $app->setIcon(base64_encode($iconRaw));
            }
        }
    }
}
