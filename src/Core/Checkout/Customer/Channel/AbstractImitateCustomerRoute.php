<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Validation\DataBag\RequestDataBag;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\ContextTokenResponse;

#[Package('checkout')]
abstract class AbstractImitateCustomerRoute
{
    abstract public function getDecorated(): AbstractImitateCustomerRoute;

    abstract public function imitateCustomerLogin(RequestDataBag $data, ChannelContext $context): ContextTokenResponse;
}
