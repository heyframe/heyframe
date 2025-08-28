<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Validation\DataBag\RequestDataBag;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\SuccessResponse;

/**
 * This route is used to send a password recovery mail
 * The required parameters are: "email" and "storefrontUrl"
 * The process can be completed with the hash in the Route HeyFrame\Core\Checkout\Customer\Channel\AbstractResetPasswordRoute
 */
#[Package('checkout')]
abstract class AbstractSendPasswordRecoveryMailRoute
{
    abstract public function getDecorated(): AbstractSendPasswordRecoveryMailRoute;

    abstract public function sendRecoveryMail(RequestDataBag $data, ChannelContext $context, bool $validateStorefrontUrl = true): SuccessResponse;
}
