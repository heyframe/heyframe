<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
abstract class AbstractCustomerGroupRegistrationSettingsRoute
{
    abstract public function getDecorated(): AbstractCustomerGroupRegistrationSettingsRoute;

    abstract public function load(string $customerGroupId, ChannelContext $context): CustomerGroupRegistrationSettingsRouteResponse;
}
