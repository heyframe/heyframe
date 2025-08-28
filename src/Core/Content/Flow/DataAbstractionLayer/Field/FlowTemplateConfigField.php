<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Flow\DataAbstractionLayer\Field;

use HeyFrame\Core\Content\Flow\DataAbstractionLayer\FieldSerializer\FlowTemplateConfigFieldSerializer;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\JsonField;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('after-sales')]
class FlowTemplateConfigField extends JsonField
{
    public function __construct(
        string $storageName,
        string $propertyName
    ) {
        $this->storageName = $storageName;
        parent::__construct($storageName, $propertyName);
    }

    protected function getSerializerClass(): string
    {
        return FlowTemplateConfigFieldSerializer::class;
    }
}
