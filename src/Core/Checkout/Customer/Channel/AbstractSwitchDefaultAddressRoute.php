<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\NoContentResponse;

/**
 * This route can be to switch the current default shipping or billing address
 */
#[Package('checkout')]
abstract class AbstractSwitchDefaultAddressRoute
{
    final public const TYPE_BILLING = 'billing';
    final public const TYPE_SHIPPING = 'shipping';

    abstract public function getDecorated(): AbstractSwitchDefaultAddressRoute;

    abstract public function swap(string $addressId, string $type, ChannelContext $context, CustomerEntity $customer): NoContentResponse;
}
