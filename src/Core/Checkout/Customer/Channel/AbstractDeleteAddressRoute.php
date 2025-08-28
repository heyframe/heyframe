<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\NoContentResponse;

/**
 * This route can be used to delete addresses
 */
#[Package('checkout')]
abstract class AbstractDeleteAddressRoute
{
    abstract public function getDecorated(): AbstractDeleteAddressRoute;

    abstract public function delete(string $addressId, ChannelContext $context, CustomerEntity $customer): NoContentResponse;
}
