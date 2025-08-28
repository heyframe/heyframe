<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\DataAbstractionLayer\CheapestPrice;

use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\WriteProtected;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\JsonField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldSerializer\PHPUnserializeFieldSerializer;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class CheapestPriceField extends JsonField
{
    public function __construct(
        string $storageName,
        string $propertyName,
        array $propertyMapping = []
    ) {
        parent::__construct($storageName, $propertyName, $propertyMapping);
        $this->addFlags(new WriteProtected());
    }

    protected function getSerializerClass(): string
    {
        return PHPUnserializeFieldSerializer::class;
    }

    protected function getAccessorBuilderClass(): ?string
    {
        return CheapestPriceAccessorBuilder::class;
    }
}
