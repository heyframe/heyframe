<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\StoreApiResponse;

/**
 * @extends StoreApiResponse<CustomerEntity>
 */
#[Package('checkout')]
class CustomerResponse extends StoreApiResponse
{
    public function getCustomer(): CustomerEntity
    {
        return $this->object;
    }
}
