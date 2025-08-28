<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Flow\Api;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Collection;

/**
 * @extends Collection<FlowActionDefinition>
 */
#[Package('after-sales')]
class FlowActionCollectorResponse extends Collection
{
    protected function getExpectedClass(): ?string
    {
        return FlowActionDefinition::class;
    }
}
