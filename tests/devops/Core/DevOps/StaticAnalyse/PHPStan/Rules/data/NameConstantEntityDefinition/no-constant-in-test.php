<?php

declare(strict_types=1);

namespace HeyFrame\Tests\Core\Framework\Foo;

use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;

class TestEntityDefinition extends EntityDefinition
{
    public function getEntityName(): string
    {
        return 'test';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([]);
    }
}
