<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Order;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\CartBehavior;
use HeyFrame\Core\Checkout\Cart\CartException;
use HeyFrame\Core\Checkout\Cart\CartRuleLoader;
use HeyFrame\Core\Checkout\Cart\Channel\CartService;
use HeyFrame\Core\Checkout\Cart\Error\Error;
use HeyFrame\Core\Checkout\Cart\Error\ErrorCollection;
use HeyFrame\Core\Checkout\Cart\Exception\CustomerNotLoggedInException;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Checkout\Cart\Processor;
use HeyFrame\Core\Checkout\CheckoutPermissions;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use HeyFrame\Core\Checkout\Order\Exception\EmptyCartException;
use HeyFrame\Core\Checkout\Order\OrderCollection;
use HeyFrame\Core\Checkout\Order\OrderEntity;
use HeyFrame\Core\Checkout\Order\OrderException;
use HeyFrame\Core\Checkout\Promotion\Cart\PromotionItemBuilder;
use HeyFrame\Core\Content\Product\Exception\ProductNotFoundException;
use HeyFrame\Core\Content\Product\ProductCollection;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Feature;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Context\ChannelContextService;

#[Package('checkout')]
class RecalculationService
{
    /**
     * @internal
     *
     * @param EntityRepository<OrderCollection> $orderRepository
     * @param EntityRepository<ProductCollection> $productRepository
     * @param EntityRepository<OrderLineItemCollection> $orderLineItemRepository
     */
    public function __construct(
        protected EntityRepository $orderRepository,
        protected OrderConverter $orderConverter,
        protected CartService $cartService,
        protected EntityRepository $productRepository,
        protected EntityRepository $orderLineItemRepository,
        protected Processor $processor,
        private readonly CartRuleLoader $cartRuleLoader,
        private readonly PromotionItemBuilder $promotionItemBuilder,
    ) {
    }

    /**
     * @param array<string, array<string, bool>|string> $channelContextOptions
     *
     * @throws CustomerNotLoggedInException
     * @throws CartException
     * @throws OrderException
     * @throws EmptyCartException
     * @throws InconsistentCriteriaIdsException
     */
    public function recalculate(string $orderId, Context $context, array $channelContextOptions = []): ErrorCollection
    {
        $order = $this->fetchOrder($orderId, $context);

        $channelContext = $this->orderConverter->assembleChannelContext($order, $context, $channelContextOptions);
        $cart = $this->orderConverter->convertToCart($order, $context);
        $recalculatedCart = $this->recalculateCart($cart, $channelContext);

        $conversionContext = $this->getOrderConversionContext();
        $orderData = $this->orderConverter->convertToOrder($recalculatedCart, $channelContext, $conversionContext);

        $this->upsertRecalculatedOrder($orderData, $order, $channelContext->getContext(), true);

        return $recalculatedCart->getErrors();
    }

    /**
     * @deprecated tag:v6.8.0 - Will be removed and is replaced by {@see recalculate}
     *
     * @param array<string, array<string, bool>|string> $channelContextOptions
     *
     * @throws CustomerNotLoggedInException
     * @throws CartException
     * @throws OrderException
     * @throws EmptyCartException
     * @throws InconsistentCriteriaIdsException
     */
    public function recalculateOrder(string $orderId, Context $context, array $channelContextOptions = []): void
    {
        Feature::triggerDeprecationOrThrow(
            'v6.8.0.0',
            Feature::deprecatedMethodMessage(self::class, __METHOD__, 'v6.8.0.0', self::class . '::recalculate')
        );

        $this->recalculate($orderId, $context, $channelContextOptions);
    }

    /**
     * @throws OrderException
     * @throws InconsistentCriteriaIdsException
     * @throws CartException
     * @throws ProductNotFoundException
     */
    public function addProductToOrder(string $orderId, string $productId, int $quantity, Context $context): void
    {
        $this->validateProduct($productId, $context);
        $lineItem = (new LineItem($productId, LineItem::PRODUCT_LINE_ITEM_TYPE, $productId, $quantity))
            ->setRemovable(true)
            ->setStackable(true);

        $order = $this->fetchOrder($orderId, $context);

        $channelContext = $this->orderConverter->assembleChannelContext($order, $context);
        $cart = $this->orderConverter->convertToCart($order, $context);
        $cart->add($lineItem);

        $recalculatedCart = $this->recalculateCart($cart, $channelContext);

        $orderData = $this->orderConverter->convertToOrder($recalculatedCart, $channelContext, $this->getOrderConversionContext());

        $this->upsertRecalculatedOrder($orderData, $order, $channelContext->getContext());
    }

    /**
     * @throws OrderException
     * @throws InconsistentCriteriaIdsException
     * @throws CartException
     */
    public function addCustomLineItem(string $orderId, LineItem $lineItem, Context $context): void
    {
        $order = $this->fetchOrder($orderId, $context);

        $channelContext = $this->orderConverter->assembleChannelContext($order, $context);
        $cart = $this->orderConverter->convertToCart($order, $context);
        $cart->add($lineItem);

        $recalculatedCart = $this->recalculateCart($cart, $channelContext);

        $conversionContext = $this->getOrderConversionContext();
        $orderData = $this->orderConverter->convertToOrder($recalculatedCart, $channelContext, $conversionContext);

        $this->upsertRecalculatedOrder($orderData, $order, $channelContext->getContext());
    }

    public function addPromotionLineItem(string $orderId, string $code, Context $context): Cart
    {
        $order = $this->fetchOrder($orderId, $context);

        $channelContext = $this->orderConverter->assembleChannelContext($order, $context);
        $cart = $this->orderConverter->convertToCart($order, $context);

        $promotionLineItem = $this->promotionItemBuilder->buildPlaceholderItem($code);

        $cart->add($promotionLineItem);
        $recalculatedCart = $this->recalculateCart($cart, $channelContext);

        $conversionContext = $this->getOrderConversionContext();
        $orderData = $this->orderConverter->convertToOrder($recalculatedCart, $channelContext, $conversionContext);

        $this->upsertRecalculatedOrder($orderData, $order, $channelContext->getContext());

        return $recalculatedCart;
    }

    public function applyAutomaticPromotions(string $orderId, Context $context): ErrorCollection
    {
        $options[ChannelContextService::PERMISSIONS] = [
            ...OrderConverter::ADMIN_EDIT_ORDER_PERMISSIONS,
            CheckoutPermissions::PIN_AUTOMATIC_PROMOTIONS => false,
        ];

        return $this->recalculate($orderId, $context, $options);
    }

    /**
     * @deprecated tag:v6.8.0 - Will be removed. Use {@see applyAutomaticPromotions} instead.
     */
    public function toggleAutomaticPromotion(string $orderId, Context $context, bool $skipAutomaticPromotions = true): Cart
    {
        Feature::triggerDeprecationOrThrow(
            'v6.8.0.0',
            Feature::deprecatedMethodMessage(self::class, __METHOD__, 'v6.8.0.0', self::class . '::applyAutomaticPromotions')
        );

        $order = $this->fetchOrder($orderId, $context);

        $options[ChannelContextService::PERMISSIONS] = [
            ...OrderConverter::ADMIN_EDIT_ORDER_PERMISSIONS,
            CheckoutPermissions::PIN_AUTOMATIC_PROMOTIONS => false,
            CheckoutPermissions::PIN_MANUAL_PROMOTIONS => false,
            CheckoutPermissions::SKIP_AUTOMATIC_PROMOTIONS => $skipAutomaticPromotions,
        ];

        $channelContext = $this->orderConverter->assembleChannelContext(
            $order,
            $context,
            $options,
        );

        $cart = $this->orderConverter->convertToCart($order, $context);

        $recalculatedCart = $this->recalculateCart($cart, $channelContext);

        $orderData = $this->orderConverter->convertToOrder($recalculatedCart, $channelContext, $this->getOrderConversionContext());

        $this->upsertRecalculatedOrder($orderData, $order, $channelContext->getContext(), true);

        return $recalculatedCart;
    }

    /**
     * @param array<string, mixed> $orderData
     */
    private function upsertRecalculatedOrder(
        array $orderData,
        OrderEntity $order,
        Context $context,
        bool $allowLineItemsDeletion = false,
    ): void {
        $orderData['id'] = $order->getId();
        $orderData['stateId'] = $order->getStateId();

        if ($allowLineItemsDeletion) {
            $this->deleteOldLineItems($orderData, $order, $context);
        }

        // change scope to be able to write protected state fields of transactions and deliveries
        $context->scope(Context::SYSTEM_SCOPE, fn (Context $context) => $this->orderRepository->upsert([$orderData], $context));
    }

    /**
     * @param array<string, mixed> $orderData
     */
    private function deleteOldLineItems(array $orderData, OrderEntity $order, Context $context): void
    {
        $newIds = \array_column($orderData['lineItems'], 'id');
        $originalIds = $order->getLineItems()?->getKeys() ?? [];
        $toDeleteIds = \array_values(\array_diff($originalIds, $newIds));

        if (\count($toDeleteIds) > 0) {
            $context->scope(Context::SYSTEM_SCOPE, fn (Context $context) => $this->orderLineItemRepository->delete(
                \array_map(static fn (string $id) => ['id' => $id], $toDeleteIds),
                $context
            ));
        }
    }

    private function fetchOrder(string $orderId, Context $context): OrderEntity
    {
        $criteria = (new Criteria([$orderId]))
            ->addAssociations([
                'primaryOrderDelivery',
                'lineItems.downloads',
                'transactions.stateMachineState',
            ]);

        $order = $this->orderRepository->search($criteria, $context)->getEntities()->first();

        $this->validateOrder($order, $orderId);

        return $order;
    }

    /**
     * @throws OrderException
     *
     * @phpstan-assert OrderEntity $order
     */
    private function validateOrder(?OrderEntity $order, string $orderId): void
    {
        if (!$order) {
            throw CartException::orderNotFound($orderId);
        }

        $this->checkVersion($order);
    }

    /**
     * @throws ProductNotFoundException
     * @throws InconsistentCriteriaIdsException
     */
    private function validateProduct(string $productId, Context $context): void
    {
        $total = $this->productRepository->searchIds(new Criteria([$productId]), $context)->getTotal();
        if ($total === 0) {
            throw CartException::productNotFound($productId);
        }
    }

    private function checkVersion(Entity $entity): void
    {
        if ($entity->getVersionId() === Defaults::LIVE_VERSION) {
            throw OrderException::canNotRecalculateLiveVersion($entity->getUniqueIdentifier());
        }
    }

    private function recalculateCart(Cart $cart, ChannelContext $context): Cart
    {
        // we switch to the live version that we don't have to consider live version fallbacks inside the calculation
        return $context->live(function ($live) use ($cart): Cart {
            /** @deprecated tag:v6.8.0 - `$isRecalculation` will be removed */
            $behavior = new CartBehavior($live->getPermissions(), true);

            // all prices are now prepared for calculation - starts the cart calculation
            $cart = $this->processor->process($cart, $live, $behavior);

            // validate cart against the context rules
            $validatedCart = $this->cartRuleLoader->loadByCart($live, $cart, $behavior)->getCart();
            $validatedCart->addErrors(...$cart->getErrors()->filter(fn (Error $error) => !$error->isPersistent()));

            return $validatedCart;
        });
    }

    private function getOrderConversionContext(): OrderConversionContext
    {
        return (new OrderConversionContext())
            ->setIncludeCustomer(false)
            ->setIncludeTransactions(false)
            ->setIncludePersistentData(false);
    }
}
