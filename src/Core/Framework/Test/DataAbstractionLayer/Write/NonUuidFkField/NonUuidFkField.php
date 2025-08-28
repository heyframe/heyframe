<?php

declare(strict_types=1);

namespace HeyFrame\Core\Framework\Test\DataAbstractionLayer\Write\NonUuidFkField;

use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;

/**
 * @internal test class
 */
class NonUuidFkField extends FkField
{
    protected function getSerializerClass(): string
    {
        return NonUuidFkFieldSerializer::class;
    }
}
