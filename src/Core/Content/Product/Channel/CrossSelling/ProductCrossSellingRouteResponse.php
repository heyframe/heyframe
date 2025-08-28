<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\CrossSelling;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\StoreApiResponse;

/**
 * @extends StoreApiResponse<CrossSellingElementCollection>
 */
#[Package('inventory')]
class ProductCrossSellingRouteResponse extends StoreApiResponse
{
    public function getResult(): CrossSellingElementCollection
    {
        return $this->object;
    }
}
