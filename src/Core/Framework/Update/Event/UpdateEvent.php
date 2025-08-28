<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Update\Event;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('framework')]
abstract class UpdateEvent extends Event
{
    public function __construct(private readonly Context $context)
    {
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
