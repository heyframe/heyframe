<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Subscriber;

use HeyFrame\Core\Framework\App\Aggregate\AppScriptCondition\AppScriptConditionEntity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('framework')]
class AppScriptConditionConstraintsSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'app_script_condition.loaded' => 'unserialize',
        ];
    }

    /**
     * @param EntityLoadedEvent<AppScriptConditionEntity> $event
     */
    public function unserialize(EntityLoadedEvent $event): void
    {
        foreach ($event->getEntities() as $entity) {
            $constraints = $entity->getConstraints();
            if ($constraints === null || !\is_string($constraints)) {
                continue;
            }

            $entity->setConstraints(unserialize($constraints));
        }
    }
}
