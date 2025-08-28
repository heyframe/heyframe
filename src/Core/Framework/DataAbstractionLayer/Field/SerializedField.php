<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Field;

use HeyFrame\Core\Framework\DataAbstractionLayer\FieldSerializer\FieldSerializerInterface;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldSerializer\JsonFieldSerializer;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class SerializedField extends Field implements StorageAware
{
    /**
     * @param class-string<FieldSerializerInterface> $serializer
     */
    public function __construct(
        private readonly string $storageName,
        string $propertyName,
        private readonly string $serializer = JsonFieldSerializer::class
    ) {
        parent::__construct($propertyName);
    }

    public function getStorageName(): string
    {
        return $this->storageName;
    }

    protected function getSerializerClass(): string
    {
        return $this->serializer;
    }
}
