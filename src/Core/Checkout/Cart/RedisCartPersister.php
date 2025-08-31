<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart;

use HeyFrame\Core\Checkout\Cart\Error\ErrorCollection;
use HeyFrame\Core\Checkout\Cart\Event\CartLoadedEvent;
use HeyFrame\Core\Checkout\Cart\Event\CartSavedEvent;
use HeyFrame\Core\Checkout\Cart\Event\CartVerifyPersistEvent;
use HeyFrame\Core\Checkout\Cart\Exception\CartTokenNotFoundException;
use HeyFrame\Core\Checkout\CheckoutPermissions;
use HeyFrame\Core\Framework\Adapter\Cache\RedisConnectionFactory;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @phpstan-import-type RedisTypeHint from RedisConnectionFactory
 */
#[Package('checkout')]
class RedisCartPersister extends AbstractCartPersister
{
    final public const PREFIX = 'cart-persister-';

    /**
     * @param RedisTypeHint $redis
     *
     * @internal
     */
    public function __construct(
        /** @phpstan-ignore heyframe.propertyNativeType (Cannot type natively, as Symfony might change the implementation in the future) */
        private $redis,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly CartSerializationCleaner $cartSerializationCleaner,
        private readonly CartCompressor $compressor,
        private readonly int $expireDays
    ) {
    }

    public function getDecorated(): AbstractCartPersister
    {
        throw new DecorationPatternException(self::class);
    }

    public function load(string $token, ChannelContext $context): Cart
    {
        $value = $this->redis->get(self::PREFIX . $token);

        if (!\is_string($value)) {
            throw CartException::tokenNotFound($token);
        }

        try {
            $value = @\unserialize($value);
        } catch (\Throwable) {
            throw CartException::tokenNotFound($token);
        }

        if (!isset($value['compressed'])) {
            throw CartException::tokenNotFound($token);
        }

        try {
            $content = $this->compressor->unserialize($value['content'], (int) $value['compressed']);
        } catch (\Throwable) {
            // When we can't decode it, we have to delete it
            throw CartException::tokenNotFound($token);
        }

        if (!\is_array($content)) {
            throw CartException::tokenNotFound($token);
        }

        $cart = $content['cart'];

        if (!$cart instanceof Cart) {
            throw CartException::deserializeFailed();
        }

        $cart->setToken($token);
        $cart->setRuleIds($content['rule_ids']);

        $this->eventDispatcher->dispatch(new CartLoadedEvent($cart, $context));

        return $cart;
    }

    public function save(Cart $cart, ChannelContext $context): void
    {
        $shouldPersist = $this->shouldPersist($cart);

        $event = new CartVerifyPersistEvent($context, $cart, $shouldPersist);

        $this->eventDispatcher->dispatch($event);
        if (!$event->shouldBePersisted()) {
            $this->delete($cart->getToken(), $context);

            return;
        }

        $content = $this->serializeCart($cart, $context);

        $this->redis->set(self::PREFIX . $cart->getToken(), $content, ['EX' => $this->expireDays * 86400]);

        $this->eventDispatcher->dispatch(new CartSavedEvent($context, $cart));
    }

    public function delete(string $token, ChannelContext $context): void
    {
        $this->redis->del(self::PREFIX . $token);
    }

    public function replace(string $oldToken, string $newToken, ChannelContext $context): void
    {
        try {
            $cart = $this->load($oldToken, $context);
        } catch (CartTokenNotFoundException) {
            return;
        }

        $copyContext = clone $context;
        $copyContext->setRuleIds($cart->getRuleIds());

        $cart->setToken($newToken);
        $this->save($cart, $copyContext);
        $cart->setToken($oldToken);

        $this->delete($oldToken, $context);
    }

    private function serializeCart(Cart $cart, ChannelContext $context): string
    {
        $errors = $cart->getErrors();
        if (!$cart->getBehavior()?->hasPermission(CheckoutPermissions::PERSIST_CART_ERRORS)) {
            $cart->setErrors(new ErrorCollection());
        }

        $data = $cart->getData();
        $cart->setData(null);

        $this->cartSerializationCleaner->cleanupCart($cart);

        [$compressed, $content] = $this->compressor->serialize(['cart' => $cart, 'rule_ids' => $context->getRuleIds()]);

        $cart->setErrors($errors);
        $cart->setData($data);

        return \serialize([
            'compressed' => $compressed,
            'content' => $content,
        ]);
    }
}
