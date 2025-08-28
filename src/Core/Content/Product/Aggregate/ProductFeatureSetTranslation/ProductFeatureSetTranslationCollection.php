<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Aggregate\ProductFeatureSetTranslation;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<ProductFeatureSetTranslationEntity>
 */
#[Package('inventory')]
class ProductFeatureSetTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ProductFeatureSetTranslationEntity::class;
    }
}
