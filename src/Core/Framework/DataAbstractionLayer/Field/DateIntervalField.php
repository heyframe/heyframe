<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Field;

use HeyFrame\Core\Framework\DataAbstractionLayer\FieldSerializer\DateIntervalFieldSerializer;
use HeyFrame\Core\Framework\Log\Package;

#[Package('checkout')]
class DateIntervalField extends Field implements StorageAware
{
    public function __construct(
        private readonly string $storageName,
        string $propertyName,
    ) {
        parent::__construct($propertyName);
    }

    public function getStorageName(): string
    {
        return $this->storageName;
    }

    protected function getSerializerClass(): string
    {
        return DateIntervalFieldSerializer::class;
    }
}
