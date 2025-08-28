<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Validation\DataBag\RequestDataBag;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\SuccessResponse;

/**
 * This route can be used to merge wishlist products from guest users to registered users.
 */
#[Package('checkout')]
abstract class AbstractMergeWishlistProductRoute
{
    abstract public function getDecorated(): AbstractMergeWishlistProductRoute;

    abstract public function merge(RequestDataBag $data, ChannelContext $context, CustomerEntity $customer): SuccessResponse;
}
