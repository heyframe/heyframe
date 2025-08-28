<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Field;

use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class ObjectField extends JsonField
{
    public function __construct(
        string $storageName,
        string $propertyName
    ) {
        parent::__construct($storageName, $propertyName);
    }
}
