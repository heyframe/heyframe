<?php declare(strict_types=1);

namespace HeyFrame\Core\System\NumberRange\DataAbstractionLayer;

use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class NumberRangeField extends StringField
{
    public function __construct(
        string $storageName,
        string $propertyName,
        int $maxLength = 64
    ) {
        parent::__construct($storageName, $propertyName, $maxLength);
    }
}
