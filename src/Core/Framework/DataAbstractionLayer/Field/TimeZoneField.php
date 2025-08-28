<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Field;

use HeyFrame\Core\Framework\DataAbstractionLayer\FieldSerializer\TimeZoneFieldSerializer;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class TimeZoneField extends StringField
{
    protected function getSerializerClass(): string
    {
        return TimeZoneFieldSerializer::class;
    }
}
