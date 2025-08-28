<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Event;

use HeyFrame\Core\Framework\Log\Package;

#[Package('fundamentals@after-sales')]
interface GenericEvent
{
    public function getName(): string;
}
