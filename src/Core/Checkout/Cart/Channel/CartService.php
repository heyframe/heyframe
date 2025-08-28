<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Channel;

use HeyFrame\Core\Checkout\Cart\AbstractCartPersister;
use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\CartCalculator;
use HeyFrame\Core\Checkout\Cart\CartException;
use HeyFrame\Core\Checkout\Cart\CartFactory;
use HeyFrame\Core\Checkout\Cart\Event\CartChangedEvent;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Validation\DataBag\RequestDataBag;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @final
 */
#[Package('checkout')]
class CartService implements ResetInterface
{
    /**
     * @var Cart[]
     */
    private array $cart = [];

    /**
     * @internal
     */
    public function __construct(
        private readonly AbstractCartPersister $persister,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly CartCalculator $calculator,
        private readonly AbstractCartLoadRoute $loadRoute,
        private readonly AbstractCartDeleteRoute $deleteRoute,
        private readonly AbstractCartItemAddRoute $itemAddRoute,
        private readonly AbstractCartItemUpdateRoute $itemUpdateRoute,
        private readonly AbstractCartItemRemoveRoute $itemRemoveRoute,
        private readonly AbstractCartOrderRoute $orderRoute,
        private readonly CartFactory $cartFactory,
    ) {
    }

    public function setCart(Cart $cart): void
    {
        $this->cart[$cart->getToken()] = $cart;
    }

    public function hasCart(string $token): bool
    {
        return isset($this->cart[$token]);
    }

    public function createNew(string $token): Cart
    {
        $cart = $this->cartFactory->createNew($token);

        return $this->cart[$cart->getToken()] = $cart;
    }

    public function getCart(
        string $token,
        ChannelContext $context,
        bool $caching = true,
        bool $taxed = false
    ): Cart {
        if ($caching && $this->hasCart($token)) {
            return $this->cart[$token];
        }

        $request = new Request();
        $request->query->set('token', $token);
        $request->query->set('taxed', $taxed);

        $cart = $this->loadRoute->load($request, $context)->getCart();

        return $this->cart[$cart->getToken()] = $cart;
    }

    /**
     * @param LineItem|LineItem[] $items
     *
     * @throws CartException
     */
    public function add(Cart $cart, LineItem|array $items, ChannelContext $context): Cart
    {
        if ($items instanceof LineItem) {
            $items = [$items];
        }

        $cart = $this->itemAddRoute->add(new Request(), $cart, $context, $items)->getCart();

        return $this->cart[$cart->getToken()] = $cart;
    }

    /**
     * @throws CartException
     */
    public function changeQuantity(Cart $cart, string $identifier, int $quantity, ChannelContext $context): Cart
    {
        return $this->update($cart, [
            [
                'id' => $identifier,
                'quantity' => $quantity,
            ],
        ], $context);
    }

    /**
     * @param array<string|int, mixed>[] $items
     *
     * @throws CartException
     */
    public function update(Cart $cart, array $items, ChannelContext $context): Cart
    {
        $request = new Request();
        $request->request->set('items', $items);

        $cart = $this->itemUpdateRoute->change($request, $cart, $context)->getCart();

        return $this->cart[$cart->getToken()] = $cart;
    }

    /**
     * @throws CartException
     */
    public function remove(Cart $cart, string $identifier, ChannelContext $context): Cart
    {
        return $this->removeItems($cart, [$identifier], $context);
    }

    /**
     * @param string[] $ids
     *
     * @throws CartException
     */
    public function removeItems(Cart $cart, array $ids, ChannelContext $context): Cart
    {
        $request = new Request();
        $request->request->set('ids', $ids);

        $cart = $this->itemRemoveRoute->remove($request, $cart, $context)->getCart();

        return $this->cart[$cart->getToken()] = $cart;
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    public function order(Cart $cart, ChannelContext $context, RequestDataBag $data): string
    {
        $orderId = $this->orderRoute->order($cart, $context, $data)->getOrder()->getId();

        if (isset($this->cart[$cart->getToken()])) {
            unset($this->cart[$cart->getToken()]);
        }

        $cart = $this->createNew($context->getToken());

        $this->eventDispatcher->dispatch(new CartChangedEvent($cart, $context));

        return $orderId;
    }

    public function recalculate(Cart $cart, ChannelContext $context): Cart
    {
        $cart = $this->calculator->calculate($cart, $context);
        $this->persister->save($cart, $context);

        return $cart;
    }

    public function deleteCart(ChannelContext $context): void
    {
        $this->deleteRoute->delete($context);
    }

    public function reset(): void
    {
        $this->cart = [];
    }
}
