<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Dbal\FieldAccessorBuilder;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use HeyFrame\Core\Framework\DataAbstractionLayer\Dbal\Exception\FieldNotStorageAwareException;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Field;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StorageAware;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('framework')]
class DefaultFieldAccessorBuilder implements FieldAccessorBuilderInterface
{
    public function buildAccessor(string $root, Field $field, Context $context, string $accessor): string
    {
        if (!$field instanceof StorageAware) {
            throw new FieldNotStorageAwareException($root . '.' . $field->getPropertyName());
        }

        return EntityDefinitionQueryHelper::escape($root) . '.' . EntityDefinitionQueryHelper::escape($field->getStorageName());
    }
}
