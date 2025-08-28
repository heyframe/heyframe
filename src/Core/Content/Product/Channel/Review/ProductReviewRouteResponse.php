<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Review;

use HeyFrame\Core\Content\Product\Aggregate\ProductReview\ProductReviewCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\StoreApiResponse;

/**
 * @extends StoreApiResponse<EntitySearchResult<ProductReviewCollection>>
 */
#[Package('after-sales')]
class ProductReviewRouteResponse extends StoreApiResponse
{
    /**
     * @return EntitySearchResult<ProductReviewCollection>
     */
    public function getResult(): EntitySearchResult
    {
        return $this->object;
    }
}
