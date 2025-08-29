<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ImportExport\DataAbstractionLayer\Serializer\Field;

use HeyFrame\Core\Content\ImportExport\DataAbstractionLayer\Serializer\SerializerRegistry;
use HeyFrame\Core\Content\ImportExport\Struct\Config;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Field;
use HeyFrame\Core\Framework\Log\Package;

#[Package('fundamentals@after-sales')]
abstract class AbstractFieldSerializer
{
    protected SerializerRegistry $serializerRegistry;

    /**
     * @param mixed $value
     *
     * @return iterable<string, mixed>
     */
    abstract public function serialize(Config $config, Field $field, $value): iterable;

    /**
     * @param mixed $value
     */
    abstract public function deserialize(Config $config, Field $field, $value): mixed;

    abstract public function supports(Field $field): bool;

    public function setRegistry(SerializerRegistry $serializerRegistry): void
    {
        $this->serializerRegistry = $serializerRegistry;
    }

    abstract public function getDecorated(): AbstractFieldSerializer;
}
