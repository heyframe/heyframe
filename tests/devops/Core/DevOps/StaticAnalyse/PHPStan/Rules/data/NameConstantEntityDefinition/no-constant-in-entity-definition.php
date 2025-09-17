<?php

declare(strict_types=1);

namespace HeyFrame\Foo\Bar;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;

class Bar extends EntityDefinition
{
    public function getEntityName(): string
    {
        return 'bar';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            new IdField('id', 'id'),
        ]);
    }
}
