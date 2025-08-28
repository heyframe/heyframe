<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Test\DataAbstractionLayer\Field\TestDefinition;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BoolField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FloatField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\JsonField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;

/**
 * @internal
 */
class NestedDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = '_test_nullable';

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
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new Required(), new PrimaryKey()),
            new JsonField('data', 'data', [
                (new FloatField('gross', 'gross'))->addFlags(new ApiAware(), new Required()),
                (new FloatField('net', 'net'))->addFlags(new ApiAware()),
                new JsonField('foo', 'foo', [
                    (new StringField('bar', 'bar'))->addFlags(new ApiAware()),
                    new JsonField('baz', 'baz', [
                        (new BoolField('deep', 'deep'))->addFlags(new ApiAware()),
                    ]),
                ]),
            ]),
        ]);
    }
}
