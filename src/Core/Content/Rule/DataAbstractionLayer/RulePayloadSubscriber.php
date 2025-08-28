<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Rule\DataAbstractionLayer;

use HeyFrame\Core\Content\Rule\RuleEntity;
use HeyFrame\Core\Content\Rule\RuleEvents;
use HeyFrame\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use HeyFrame\Core\Framework\Rule\Container\Container;
use HeyFrame\Core\Framework\Rule\Container\FilterRule;
use HeyFrame\Core\Framework\Rule\Rule;
use HeyFrame\Core\Framework\Rule\ScriptRule;
use HeyFrame\Core\Framework\Script\Debugging\ScriptTraces;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
class RulePayloadSubscriber implements EventSubscriberInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly RulePayloadUpdater $updater,
        private readonly ScriptTraces $traces,
        private readonly string $cacheDir,
        private readonly bool $debug
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RuleEvents::RULE_LOADED_EVENT => 'unserialize',
        ];
    }

    /**
     * @param EntityLoadedEvent<RuleEntity> $event
     */
    public function unserialize(EntityLoadedEvent $event): void
    {
        $this->indexIfNeeded($event);

        foreach ($event->getEntities() as $entity) {
            $payload = $entity->getPayload();
            if ($payload === null || !\is_string($payload)) {
                continue;
            }

            $payload = unserialize($payload);

            $this->enrichConditions([$payload]);

            $entity->setPayload($payload);
        }
    }

    /**
     * @param EntityLoadedEvent<RuleEntity> $event
     */
    private function indexIfNeeded(EntityLoadedEvent $event): void
    {
        $rules = [];

        foreach ($event->getEntities() as $rule) {
            if ($rule->getPayload() === null && !$rule->isInvalid()) {
                $rules[$rule->getId()] = $rule;
            }
        }

        if (!\count($rules)) {
            return;
        }

        $updated = $this->updater->update(array_keys($rules));

        foreach ($updated as $id => $entity) {
            $rules[$id]->assign($entity);
        }
    }

    /**
     * @param list<Rule> $conditions
     */
    private function enrichConditions(array $conditions): void
    {
        foreach ($conditions as $condition) {
            if ($condition instanceof ScriptRule) {
                $condition->assign([
                    'traces' => $this->traces,
                    'cacheDir' => $this->cacheDir,
                    'debug' => $this->debug,
                ]);

                continue;
            }

            if ($condition instanceof Container || $condition instanceof FilterRule) {
                $this->enrichConditions($condition->getRules());
            }
        }
    }
}
