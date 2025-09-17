<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Framework\Api\ApiDefinition\Generator\_fixtures;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BoolField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ChildCountField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Runtime;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Since;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\WriteProtected;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FloatField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IntField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;

/**
 * @internal
 */
class SimpleDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'simple';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection(
            [
                (new StringField('string_field', 'stringField'))->addFlags(new ApiAware()),
                (new IntField('int_field', 'intField'))->addFlags(new ApiAware()),
                (new FloatField('float_field', 'floatField'))->addFlags(new ApiAware()),
                (new BoolField('bool_field', 'boolField'))->addFlags(new ApiAware()),
                (new IdField('id_field', 'idField'))->addFlags(new ApiAware()),
                (new StringField('i_am_a_new_field', 'i_am_a_new_field'))->addFlags(new ApiAware(), new Since('6.3.9.9')),
                (new ChildCountField())->addFlags(new ApiAware()),

                (new StringField('required_field', 'requiredField'))->addFlags(new ApiAware(), new Required()),
                (new StringField('read_only_field', 'readOnlyField'))->addFlags(new ApiAware(), new WriteProtected()),
                (new StringField('runtime_field', 'runtimeField'))->addFlags(new ApiAware(), new Runtime()),
            ]
        );
    }
}
