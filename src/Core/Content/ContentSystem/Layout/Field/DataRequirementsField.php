<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Layout\Field;

use HeyFrame\Core\Framework\DataAbstractionLayer\Field\JsonField;
use HeyFrame\Core\Framework\Log\Package;

/**
 * Field for storing data requirements map as typed array<string, DataRequirement>.
 *
 * Handles conversion between JSON storage and array of DataRequirement objects during
 * entity hydration/persistence.
 *
 * @internal
 */
#[Package('discovery')]
class DataRequirementsField extends JsonField
{
    protected function getSerializerClass(): string
    {
        return DataRequirementsFieldSerializer::class;
    }
}
