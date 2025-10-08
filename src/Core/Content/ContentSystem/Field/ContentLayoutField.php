<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Field;

use HeyFrame\Core\Framework\DataAbstractionLayer\Field\JsonField;
use HeyFrame\Core\Framework\Log\Package;

/**
 * Field for storing Content Layout structure as typed ContentElement tree.
 *
 * This field serializes ContentElement objects (or arrays) to JSON for database storage
 * and deserializes JSON back to ContentElement objects during entity hydration.
 *
 * Uses ContentLayoutFieldSerializer for JSON ↔ ContentElement conversion.
 *
 * @internal
 */
#[Package('discovery')]
class ContentLayoutField extends JsonField
{
}
