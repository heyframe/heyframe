<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Validation\DataBag\RequestDataBag;
use HeyFrame\Core\Framework\Validation\DataValidationDefinition;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * This route is used for customer registration
 * The required parameters are: "salutationId", "firstName", "lastName", "email", "password", "billingAddress" and "storefrontUrl"
 * The "billingAddress" should has required parameters: "salutationId", "firstName", "lastName", "street", "zipcode", "city", "countyId".
 */
#[Package('checkout')]
abstract class AbstractRegisterRoute
{
    abstract public function getDecorated(): AbstractRegisterRoute;

    abstract public function register(RequestDataBag $data, ChannelContext $context, bool $validateStorefrontUrl = true, ?DataValidationDefinition $additionalValidationDefinitions = null): CustomerResponse;
}
