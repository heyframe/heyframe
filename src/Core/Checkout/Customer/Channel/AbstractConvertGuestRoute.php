<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Validation\DataBag\RequestDataBag;
use HeyFrame\Core\Framework\Validation\DataValidationDefinition;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\SuccessResponse;

/**
 * This route is used to set a password for a guest user and convert it to a registered one.
 */
#[Package('checkout')]
abstract class AbstractConvertGuestRoute
{
    abstract public function getDecorated(): AbstractConvertGuestRoute;

    abstract public function convertGuest(RequestDataBag $requestDataBag, ChannelContext $context, CustomerEntity $customer, ?DataValidationDefinition $additionalValidationDefinitions = null): SuccessResponse;
}
