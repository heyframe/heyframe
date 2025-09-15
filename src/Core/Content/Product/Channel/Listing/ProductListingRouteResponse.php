<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Listing;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\FrontApiResponse;

/**
 * @extends FrontApiResponse<ProductListingResult>
 */
#[Package('inventory')]
class ProductListingRouteResponse extends FrontApiResponse
{
    public function getResult(): ProductListingResult
    {
        return $this->object;
    }
}
