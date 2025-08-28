<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Validation\DataBag\RequestDataBag;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * This route can be used to create or update new customer addresses
 */
#[Package('checkout')]
abstract class AbstractUpsertAddressRoute
{
    abstract public function upsert(?string $addressId, RequestDataBag $data, ChannelContext $context, CustomerEntity $customer): UpsertAddressRouteResponse;

    abstract public function getDecorated(): AbstractUpsertAddressRoute;
}
