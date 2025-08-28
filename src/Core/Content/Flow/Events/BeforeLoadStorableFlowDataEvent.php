<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Flow\Events;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Event\GenericEvent;
use HeyFrame\Core\Framework\Event\HeyFrameEvent;
use Symfony\Contracts\EventDispatcher\Event;

class BeforeLoadStorableFlowDataEvent extends Event implements HeyFrameEvent, GenericEvent
{
    public function __construct(
        private readonly string $entityName,
        private readonly Criteria $criteria,
        private readonly Context $context,
    ) {
    }

    public function getName(): string
    {
        return 'flow.storer.' . $this->entityName . '.criteria.event';
    }

    public function getCriteria(): Criteria
    {
        return $this->criteria;
    }

    public function getEntityName(): string
    {
        return $this->entityName;
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
