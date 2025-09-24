<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Validation\DataBag\RequestDataBag;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\ContextTokenResponse;

/**
 * This route is used to change the password of a logged-in user
 * The required fields are: "password", "newPassword" and "newPasswordConfirm"
 */
#[Package('checkout')]
abstract class AbstractChangePasswordRoute
{
    abstract public function getDecorated(): AbstractChangePasswordRoute;

    abstract public function change(RequestDataBag $requestDataBag, ChannelContext $context, CustomerEntity $customer): ContextTokenResponse;
}
