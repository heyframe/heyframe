<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Aggregate\ProductFeatureSet;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<ProductFeatureSetEntity>
 */
#[Package('inventory')]
class ProductFeatureSetCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ProductFeatureSetEntity::class;
    }
}
