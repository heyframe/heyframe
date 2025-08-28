<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\SuccessResponse;

#[Package('checkout')]
abstract class AbstractRemoveWishlistProductRoute
{
    abstract public function getDecorated(): AbstractRemoveWishlistProductRoute;

    abstract public function delete(string $productId, ChannelContext $context, CustomerEntity $customer): SuccessResponse;
}
