<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product;

use HeyFrame\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use HeyFrame\Core\Content\Property\PropertyGroupCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\PartialEntity;
use HeyFrame\Core\Framework\Log\Package;

#[Package('inventory')]
abstract class AbstractPropertyGroupSorter
{
    abstract public function getDecorated(): AbstractPropertyGroupSorter;

    /**
     * @param EntityCollection<PropertyGroupOptionEntity|PartialEntity> $options
     */
    abstract public function sort(EntityCollection $options): PropertyGroupCollection;
}
