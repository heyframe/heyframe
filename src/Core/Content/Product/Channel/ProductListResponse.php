<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel;

use HeyFrame\Core\Content\Product\ProductCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\StoreApiResponse;

/**
 * @extends StoreApiResponse<EntitySearchResult<ProductCollection>>
 */
#[Package('inventory')]
class ProductListResponse extends StoreApiResponse
{
    public function getProducts(): ProductCollection
    {
        return $this->object->getEntities();
    }
}
