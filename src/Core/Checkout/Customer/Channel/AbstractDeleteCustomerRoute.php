<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\NoContentResponse;

/**
 * This route can be used to delete a customer
 */
#[Package('checkout')]
abstract class AbstractDeleteCustomerRoute
{
    abstract public function getDecorated(): AbstractDeleteCustomerRoute;

    abstract public function delete(ChannelContext $context, CustomerEntity $customer): NoContentResponse;
}
