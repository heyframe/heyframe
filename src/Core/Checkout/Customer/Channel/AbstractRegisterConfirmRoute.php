<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Validation\DataBag\RequestDataBag;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * This route can be used to complete the double optin registration.
 * The required parameters are: "hash" (received from the mail) and "em" (received from the mail)
 */
#[Package('checkout')]
abstract class AbstractRegisterConfirmRoute
{
    abstract public function getDecorated(): AbstractRegisterConfirmRoute;

    abstract public function confirm(RequestDataBag $dataBag, ChannelContext $context): CustomerResponse;
}
