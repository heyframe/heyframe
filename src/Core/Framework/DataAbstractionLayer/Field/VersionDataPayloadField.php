<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Field;

use HeyFrame\Core\Framework\DataAbstractionLayer\FieldSerializer\VersionDataPayloadFieldSerializer;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('framework')]
class VersionDataPayloadField extends JsonField
{
    protected function getSerializerClass(): string
    {
        return VersionDataPayloadFieldSerializer::class;
    }
}
