<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Validation\DataBag\RequestDataBag;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\SuccessResponse;

/**
 * This route is used handle the password reset form
 * The required parameters are: "hash" (received from the mail), "newPassword" and "newPasswordConfirm"
 */
#[Package('checkout')]
abstract class AbstractResetPasswordRoute
{
    abstract public function getDecorated(): AbstractResetPasswordRoute;

    abstract public function resetPassword(RequestDataBag $data, ChannelContext $context): SuccessResponse;
}
