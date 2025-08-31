<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Field;

use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldSerializer\CartPriceFieldSerializer;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class CartPriceField extends JsonField
{
    public function __construct(
        string $storageName,
        string $propertyName
    ) {
        $propertyMapping = [
            (new FloatField('totalPrice', 'totalPrice'))->addFlags(new Required()),
            (new FloatField('positionPrice', 'positionPrice'))->addFlags(new Required()),
            (new FloatField('rawTotal', 'rawTotal'))->setFlags(new Required()),
        ];

        parent::__construct($storageName, $propertyName, $propertyMapping);
    }

    protected function getSerializerClass(): string
    {
        return CartPriceFieldSerializer::class;
    }
}
