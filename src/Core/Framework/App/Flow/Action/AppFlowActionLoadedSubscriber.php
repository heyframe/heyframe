<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Flow\Action;

use HeyFrame\Core\Framework\App\Aggregate\FlowAction\AppFlowActionEntity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('framework')]
class AppFlowActionLoadedSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'app_flow_action.loaded' => 'unserialize',
        ];
    }

    /**
     * @param EntityLoadedEvent<AppFlowActionEntity> $event
     */
    public function unserialize(EntityLoadedEvent $event): void
    {
        foreach ($event->getEntities() as $appFlowAction) {
            $iconRaw = $appFlowAction->getIconRaw();

            if ($iconRaw !== null) {
                $appFlowAction->setIcon(base64_encode($iconRaw));
            }
        }
    }
}
