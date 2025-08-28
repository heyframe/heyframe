<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\Lock\LockFactory;

/**
 * @internal
 */
#[Package('checkout')]
class CartLocker
{
    private const LOCK_TTL = 5;

    public function __construct(private readonly LockFactory $lockFactory)
    {
    }

    /**
     * @template T
     *
     * @param \Closure(): T $closure
     *
     * @return T
     */
    public function locked(ChannelContext $context, \Closure $closure)
    {
        if ($context->getCartLock()?->isAcquired()) {
            // If the lock is already acquired for this context & process, we can skip acquiring it again
            return $closure();
        }

        $lockKey = $this->getLockKey($context->getToken());
        $lock = $this->lockFactory->createLock($lockKey, self::LOCK_TTL);

        if (!$lock->acquire()) {
            throw CartException::cartLocked($context->getToken());
        }

        try {
            $context->setCartLock($lock);

            return $closure();
        } finally {
            $lock->release();
            $context->setCartLock(null);
        }
    }

    public function getLockKey(string $token): string
    {
        return 'cart-lock' . $token;
    }
}
