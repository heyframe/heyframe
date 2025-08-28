<?php

declare(strict_types=1);

namespace HeyFrame\Core\Framework\Api\HealthCheck\Event;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('framework')]
class HealthCheckEvent extends Event
{
    public function __construct(
        public readonly Context $context
    ) {
    }
}
