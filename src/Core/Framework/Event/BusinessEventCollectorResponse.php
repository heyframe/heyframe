<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Event;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Collection;

/**
 * @extends Collection<BusinessEventDefinition>
 */
#[Package('fundamentals@after-sales')]
class BusinessEventCollectorResponse extends Collection
{
    protected function getExpectedClass(): ?string
    {
        return BusinessEventDefinition::class;
    }
}
