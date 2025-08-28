<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Events;

use HeyFrame\Core\Content\Product\DataAbstractionLayer\UpdatedStates;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\HeyFrameEvent;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('inventory')]
class ProductStatesChangedEvent extends Event implements HeyFrameEvent
{
    /**
     * @param UpdatedStates[] $updatedStates
     */
    public function __construct(
        protected array $updatedStates,
        protected Context $context
    ) {
    }

    /**
     * @return UpdatedStates[]
     */
    public function getUpdatedStates(): array
    {
        return $this->updatedStates;
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
