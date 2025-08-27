<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Field;

use HeyFrame\Core\Framework\DataAbstractionLayer\Dbal\FieldAccessorBuilder\JsonFieldAccessorBuilder;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldSerializer\CashRoundingConfigFieldSerializer;

class CashRoundingConfigField extends JsonField
{
    public function __construct(
        string $storageName,
        string $propertyName
    ) {
        parent::__construct($storageName, $propertyName, [
            (new IntField('decimals', 'decimals', 0))->addFlags(new Required()),
            (new FloatField('interval', 'interval'))->addFlags(new Required()),
            (new BoolField('roundForNet', 'roundForNet'))->addFlags(new Required()),
        ]);
    }

    protected function getSerializerClass(): string
    {
        return CashRoundingConfigFieldSerializer::class;
    }

    protected function getAccessorBuilderClass(): ?string
    {
        return JsonFieldAccessorBuilder::class;
    }
}
