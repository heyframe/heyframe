<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Search;

use HeyFrame\Core\Content\Product\Channel\Listing\ProductListingResult;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\StoreApiResponse;

/**
 * @extends StoreApiResponse<ProductListingResult>
 */
#[Package('inventory')]
class ProductSearchRouteResponse extends StoreApiResponse
{
    public function getListingResult(): ProductListingResult
    {
        return $this->object;
    }
}
