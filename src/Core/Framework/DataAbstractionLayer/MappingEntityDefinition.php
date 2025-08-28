<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer;

use HeyFrame\Core\Framework\DataAbstractionLayer\Exception\MappingEntityClassesException;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
abstract class MappingEntityDefinition extends EntityDefinition
{
    public function getCollectionClass(): string
    {
        throw new MappingEntityClassesException();
    }

    public function getEntityClass(): string
    {
        throw new MappingEntityClassesException();
    }

    protected function getBaseFields(): array
    {
        return [];
    }

    protected function defaultFields(): array
    {
        return [];
    }
}
