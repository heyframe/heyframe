<?php

declare(strict_types=1);

namespace HeyFrame\Core\Framework\Test\DataAbstractionLayer\Write\NonUuidFkField;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;

/**
 * @internal test class
 */
class TestEntityOneDefinition extends EntityDefinition
{
    public function getEntityName(): string
    {
        return 'test_entity_one';
    }

    public function since(): ?string
    {
        return 'test';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('technical_name', 'technicalName'))->addFlags(new PrimaryKey(), new Required()),
        ]);
    }
}
