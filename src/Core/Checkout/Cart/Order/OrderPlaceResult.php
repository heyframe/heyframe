<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Order;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Struct;

#[Package('checkout')]
class OrderPlaceResult extends Struct
{
    public function __construct(public string $orderId)
    {
    }
}
