<?php

declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Foo;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;

class Bar
{
    public function foo(): EntityDefinition
    {
        return new class extends EntityDefinition {
            public function getEntityName(): string
            {
                return 'ccc';
            }

            protected function defineFields(): FieldCollection
            {
                return new FieldCollection(
                    [new StringField('aaa', 'foo')]
                );
            }
        };
    }
}
