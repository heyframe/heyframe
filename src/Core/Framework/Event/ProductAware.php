<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Event;

use HeyFrame\Core\Framework\Log\Package;

#[Package('after-sales')]
#[IsFlowEventAware]
interface ProductAware
{
    public const PRODUCT = 'product';

    public const PRODUCT_ID = 'productId';

    public function getProductId(): string;
}
