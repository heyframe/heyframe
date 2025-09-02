<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart;

use HeyFrame\Core\Checkout\Cart\Event\CartContextHashEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Util\Hasher;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Package('checkout')]
class CartContextHasher
{
    /**
     * @internal
     */
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function isMatching(string $hash, Cart $cart, ChannelContext $context): bool
    {
        return $hash === $this->generate($cart, $context);
    }

    /**
     * @throws \JsonException
     */
    public function generate(Cart $cart, ChannelContext $context): string
    {
        $struct = new CartContextHashStruct();

        $struct->setPrice($cart->getPrice()->getRawTotal());
        $struct->setPaymentMethod($context->getPaymentMethod()->getId());

        foreach ($cart->getLineItems()->getElements() as $item) {
            $struct->addLineItem($item->getId(), $item->getHashContent());
        }

        $event = $this
            ->eventDispatcher
            ->dispatch(new CartContextHashEvent($context, $cart, $struct));

        return Hasher::hash($event->getHashStruct(), 'sha256');
    }
}
