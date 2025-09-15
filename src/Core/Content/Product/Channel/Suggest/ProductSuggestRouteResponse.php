<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Suggest;

use HeyFrame\Core\Content\Product\Channel\Listing\ProductListingResult;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\FrontApiResponse;

/**
 * @extends FrontApiResponse<ProductListingResult>
 */
#[Package('discovery')]
class ProductSuggestRouteResponse extends FrontApiResponse
{
    public function getListingResult(): ProductListingResult
    {
        return $this->object;
    }
}
