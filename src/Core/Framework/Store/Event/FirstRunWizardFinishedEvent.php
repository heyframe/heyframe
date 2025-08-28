<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Store\Event;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Store\Struct\FrwState;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @internal
 */
#[Package('fundamentals@after-sales')]
class FirstRunWizardFinishedEvent extends Event
{
    public function __construct(
        private readonly FrwState $state,
        private readonly FrwState $previousState,
        private readonly Context $context
    ) {
    }

    public function getState(): FrwState
    {
        return $this->state;
    }

    public function getPreviousState(): FrwState
    {
        return $this->previousState;
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
