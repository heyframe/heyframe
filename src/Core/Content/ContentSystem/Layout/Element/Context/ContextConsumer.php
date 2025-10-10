<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Layout\Element\Context;

use HeyFrame\Core\Content\ContentSystem\Hydration\DataContext\ContextType;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('discovery')]
class ContextConsumer
{
    public function __construct(
        public readonly ContextType $type,
        public readonly bool $required
    ) {
    }
}
