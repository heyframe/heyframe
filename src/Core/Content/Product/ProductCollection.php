<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<ProductEntity>
 */
#[Package('inventory')]
class ProductCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'product_collection';
    }

    protected function getExpectedClass(): string
    {
        return ProductEntity::class;
    }
}
