<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Write\Validation;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Write\Command\WriteCommand;
use HeyFrame\Core\Framework\Event\HeyFrameEvent;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('framework')]
class WriteCommandExceptionEvent extends Event implements HeyFrameEvent
{
    /**
     * @param WriteCommand[] $commands
     */
    public function __construct(
        private readonly \Throwable $exception,
        private readonly array $commands,
        private readonly Context $context
    ) {
    }

    public function getException(): \Throwable
    {
        return $this->exception;
    }

    public function getCommands(): array
    {
        return $this->commands;
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
