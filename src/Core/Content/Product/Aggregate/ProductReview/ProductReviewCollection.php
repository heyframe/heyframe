<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Aggregate\ProductReview;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<ProductReviewEntity>
 */
#[Package('after-sales')]
class ProductReviewCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'product_review_collection';
    }

    protected function getExpectedClass(): string
    {
        return ProductReviewEntity::class;
    }
}
