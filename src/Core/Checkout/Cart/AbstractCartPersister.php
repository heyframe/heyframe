<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart;

use HeyFrame\Core\Checkout\CheckoutPermissions;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
abstract class AbstractCartPersister
{
    abstract public function getDecorated(): AbstractCartPersister;

    abstract public function load(string $token, ChannelContext $context): Cart;

    abstract public function save(Cart $cart, ChannelContext $context): void;

    abstract public function delete(string $token, ChannelContext $context): void;

    abstract public function replace(string $oldToken, string $newToken, ChannelContext $context): void;

    /**
     * This method is called by the cleanup task handler to remove old carts from the database.
     * The cart persisted should implement this method to remove carts that are older than the given amount of days.
     */
    public function prune(int $days): void
    {
    }

    protected function shouldPersist(Cart $cart): bool
    {
        return ($cart->getLineItems()->count() > 0
            || ($cart->getErrors()->count() > 0 && $cart->getBehavior()?->hasPermission(CheckoutPermissions::PERSIST_CART_ERRORS)))
            && !$cart->getBehavior()?->hasPermission(CheckoutPermissions::SKIP_CART_PERSISTENCE);
    }
}
