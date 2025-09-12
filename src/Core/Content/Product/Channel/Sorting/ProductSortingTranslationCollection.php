<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Sorting;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<ProductSortingTranslationEntity>
 */
#[Package('inventory')]
class ProductSortingTranslationCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'product_sorting_translation_collection';
    }

    protected function getExpectedClass(): string
    {
        return ProductSortingTranslationEntity::class;
    }
}
