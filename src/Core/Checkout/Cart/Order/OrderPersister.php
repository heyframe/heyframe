<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Order;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\CartException;
use HeyFrame\Core\Checkout\Cart\CartSerializationCleaner;
use HeyFrame\Core\Checkout\Cart\Exception\InvalidCartException;
use HeyFrame\Core\Checkout\Order\OrderCollection;
use HeyFrame\Core\Checkout\Order\OrderException;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
class OrderPersister implements OrderPersisterInterface
{
    /**
     * @internal
     *
     * @param EntityRepository<OrderCollection> $orderRepository
     */
    public function __construct(
        private readonly EntityRepository $orderRepository,
        private readonly OrderConverter $converter,
        private readonly CartSerializationCleaner $cartSerializationCleaner,
    ) {
    }

    /**
     * @throws CartException
     * @throws OrderException
     * @throws InvalidCartException
     * @throws InconsistentCriteriaIdsException
     */
    public function persist(Cart $cart, ChannelContext $context): string
    {
        if ($cart->getErrors()->blockOrder()) {
            throw CartException::invalidCart($cart->getErrors());
        }

        if (!$context->getCustomer()) {
            throw CartException::customerNotLoggedIn();
        }

        if ($cart->getLineItems()->count() <= 0) {
            throw CartException::cartEmpty();
        }

        // cleanup cart before converting it to an order
        $this->cartSerializationCleaner->cleanupCart($cart);

        $order = $this->converter->convertToOrder($cart, $context, new OrderConversionContext());

        $context->getContext()->scope(Context::SYSTEM_SCOPE, function (Context $context) use ($order): void {
            $this->orderRepository->create([$order], $context);
        });

        return $order['id'];
    }
}
