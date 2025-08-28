<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Validation\DataBag\RequestDataBag;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\SuccessResponse;

/**
 * This route can be used to change profile information about the logged-in user
 * The required fields are "salutationId", "firstName" and "lastName"
 */
#[Package('checkout')]
abstract class AbstractChangeCustomerProfileRoute
{
    abstract public function getDecorated(): AbstractChangeCustomerProfileRoute;

    abstract public function change(RequestDataBag $data, ChannelContext $context, CustomerEntity $customer): SuccessResponse;
}
