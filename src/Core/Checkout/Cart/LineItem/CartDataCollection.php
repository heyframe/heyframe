<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\LineItem;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Collection;

/**
 * @extends Collection<mixed>
 */
#[Package('checkout')]
class CartDataCollection extends Collection
{
    public function getApiAlias(): string
    {
        return 'cart_data_collection';
    }
}
