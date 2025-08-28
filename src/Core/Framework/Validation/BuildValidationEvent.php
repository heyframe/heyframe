<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Validation;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\GenericEvent;
use HeyFrame\Core\Framework\Event\HeyFrameEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Validation\DataBag\DataBag;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('framework')]
class BuildValidationEvent extends Event implements HeyFrameEvent, GenericEvent
{
    public function __construct(
        private readonly DataValidationDefinition $definition,
        private readonly DataBag $data,
        private readonly Context $context
    ) {
    }

    public function getName(): string
    {
        return 'framework.validation.' . $this->definition->getName();
    }

    public function getDefinition(): DataValidationDefinition
    {
        return $this->definition;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getData(): DataBag
    {
        return $this->data;
    }
}
