<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Validation\DataBag\RequestDataBag;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\SuccessResponse;

/**
 * This route is used to change the language of a logged-in user
 * The required field is: "languageId"
 */
#[Package('checkout')]
abstract class AbstractChangeLanguageRoute
{
    abstract public function getDecorated(): AbstractChangeLanguageRoute;

    abstract public function change(RequestDataBag $requestDataBag, ChannelContext $context, CustomerEntity $customer): SuccessResponse;
}
