<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Channel;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\StoreApiResponse;

/**
 * @extends StoreApiResponse<Cart>
 */
#[Package('checkout')]
class CartResponse extends StoreApiResponse
{
    public function getCart(): Cart
    {
        return $this->object;
    }
}
