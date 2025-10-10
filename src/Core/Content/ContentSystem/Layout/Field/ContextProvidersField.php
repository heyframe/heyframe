<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Layout\Field;

use HeyFrame\Core\Framework\DataAbstractionLayer\Field\JsonField;
use HeyFrame\Core\Framework\Log\Package;

/**
 * Field for storing context providers map.
 *
 * Handles conversion between JSON storage and array<string, ContextProvider>
 * during entity hydration/persistence.
 *
 * @internal
 */
#[Package('discovery')]
class ContextProvidersField extends JsonField
{
    protected function getSerializerClass(): string
    {
        return ContextProvidersFieldSerializer::class;
    }
}
