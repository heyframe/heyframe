<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Field;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class ExtraFields extends JsonField
{
    public function __construct(
        string $storageName = 'extra_fields',
        string $propertyName = 'extraFields'
    ) {
        parent::__construct($storageName, $propertyName);
    }

}
