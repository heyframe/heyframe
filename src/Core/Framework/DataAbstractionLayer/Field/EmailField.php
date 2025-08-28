<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Field;

use HeyFrame\Core\Framework\DataAbstractionLayer\FieldSerializer\EmailFieldSerializer;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class EmailField extends StringField
{
    protected function getSerializerClass(): string
    {
        return EmailFieldSerializer::class;
    }
}
