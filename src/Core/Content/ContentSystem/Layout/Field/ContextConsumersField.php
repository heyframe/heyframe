<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Layout\Field;

use HeyFrame\Core\Framework\DataAbstractionLayer\Field\JsonField;
use HeyFrame\Core\Framework\Log\Package;

/**
 * Field for storing context consumers map.
 *
 * Handles conversion between JSON storage and array<string, ContextConsumer>
 * during entity hydration/persistence.
 *
 * @internal
 */
#[Package('discovery')]
class ContextConsumersField extends JsonField
{
    protected function getSerializerClass(): string
    {
        return ContextConsumersFieldSerializer::class;
    }
}
