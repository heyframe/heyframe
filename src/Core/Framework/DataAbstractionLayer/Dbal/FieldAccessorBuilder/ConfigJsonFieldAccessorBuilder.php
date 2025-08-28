<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Dbal\FieldAccessorBuilder;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ConfigJsonField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Field;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('framework')]
class ConfigJsonFieldAccessorBuilder extends JsonFieldAccessorBuilder
{
    public function buildAccessor(string $root, Field $field, Context $context, string $accessor): ?string
    {
        if (!$field instanceof ConfigJsonField) {
            return null;
        }

        $jsonPath = preg_replace(
            '#^' . preg_quote($field->getPropertyName(), '#') . '#',
            '',
            $accessor
        );

        $accessor = $field->getPropertyName() . '.' . ConfigJsonField::STORAGE_KEY . $jsonPath;

        return parent::buildAccessor($root, $field, $context, $accessor);
    }
}
