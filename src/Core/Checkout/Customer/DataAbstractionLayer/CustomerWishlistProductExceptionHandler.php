<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\DataAbstractionLayer;

use HeyFrame\Core\Checkout\Customer\CustomerException;
use HeyFrame\Core\Framework\DataAbstractionLayer\Dbal\ExceptionHandlerInterface;
use HeyFrame\Core\Framework\Log\Package;

#[Package('checkout')]
class CustomerWishlistProductExceptionHandler implements ExceptionHandlerInterface
{
    public function getPriority(): int
    {
        return ExceptionHandlerInterface::PRIORITY_DEFAULT;
    }

    public function matchException(\Throwable $e): ?\Throwable
    {
        if (preg_match('/SQLSTATE\[23000\]:.*1062 Duplicate.*uniq.customer_wishlist.channel_id__customer_id\'/', $e->getMessage())) {
            return CustomerException::duplicateWishlistProduct();
        }

        return null;
    }
}
