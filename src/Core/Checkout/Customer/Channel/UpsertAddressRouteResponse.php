<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressEntity;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\StoreApiResponse;

/**
 * @extends StoreApiResponse<CustomerAddressEntity>
 */
#[Package('checkout')]
class UpsertAddressRouteResponse extends StoreApiResponse
{
    public function getAddress(): CustomerAddressEntity
    {
        return $this->object;
    }
}
