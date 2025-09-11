<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ProductStream\Aggregate\ProductStreamFilter;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<ProductStreamFilterEntity>
 */
#[Package('inventory')]
class ProductStreamFilterCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'product_stream_filter_collection';
    }

    protected function getExpectedClass(): string
    {
        return ProductStreamFilterEntity::class;
    }
}
