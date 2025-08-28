<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\LineItem\Group;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItemCollection;
use HeyFrame\Core\Framework\Log\Package;

#[Package('checkout')]
abstract class AbstractProductLineItemProvider
{
    abstract public function getDecorated(): AbstractProductLineItemProvider;

    abstract public function getProducts(Cart $cart): LineItemCollection;
}
