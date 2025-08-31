<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Field;

use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldSerializer\CalculatedPriceFieldSerializer;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class CalculatedPriceField extends JsonField
{
    public function __construct(
        string $storageName,
        string $propertyName
    ) {
        $propertyMapping = [
            (new FloatField('unitPrice', 'unitPrice'))->addFlags(new Required()),
            (new FloatField('totalPrice', 'totalPrice'))->addFlags(new Required()),
            (new IntField('quantity', 'quantity'))->addFlags(new Required()),
            new JsonField('referencePrice', 'referencePrice'),
            new JsonField('listPrice', 'listPrice', [
                new FloatField('price', 'price'),
                new FloatField('discount', 'discount'),
                new FloatField('percentage', 'percentage'),
            ]),
            new JsonField('regulationPrice', 'regulationPrice', [
                new FloatField('price', 'price'),
            ]),
        ];

        parent::__construct($storageName, $propertyName, $propertyMapping);
    }

    protected function getSerializerClass(): string
    {
        return CalculatedPriceFieldSerializer::class;
    }
}
