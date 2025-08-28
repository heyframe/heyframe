<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Delivery;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\CartBehavior;
use HeyFrame\Core\Checkout\Cart\CartDataCollectorInterface;
use HeyFrame\Core\Checkout\Cart\CartProcessorInterface;
use HeyFrame\Core\Checkout\Cart\Delivery\Struct\Delivery;
use HeyFrame\Core\Checkout\Cart\LineItem\CartDataCollection;
use HeyFrame\Core\Checkout\Cart\Order\IdStruct;
use HeyFrame\Core\Checkout\Cart\Order\OrderConverter;
use HeyFrame\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use HeyFrame\Core\Checkout\CheckoutPermissions;
use HeyFrame\Core\Checkout\Shipping\ShippingMethodCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Feature;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Profiling\Profiler;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
class DeliveryProcessor implements CartProcessorInterface, CartDataCollectorInterface
{
    final public const MANUAL_SHIPPING_COSTS = 'manualShippingCosts';

    /**
     * @deprecated tag:v6.8.0 - Will be removed and is replaced by {@see CheckoutPermissions::SKIP_PRODUCT_STOCK_VALIDATION}
     */
    final public const SKIP_DELIVERY_PRICE_RECALCULATION = CheckoutPermissions::SKIP_DELIVERY_PRICE_RECALCULATION;

    /**
     * @deprecated tag:v6.8.0 - Will be removed and is replaced by {@see CheckoutPermissions::SKIP_DELIVERY_TAX_RECALCULATION}
     */
    final public const SKIP_DELIVERY_TAX_RECALCULATION = CheckoutPermissions::SKIP_DELIVERY_TAX_RECALCULATION;

    /**
     * @internal
     *
     * @param EntityRepository<ShippingMethodCollection> $shippingMethodRepository
     */
    public function __construct(
        protected DeliveryBuilder $builder,
        protected DeliveryCalculator $deliveryCalculator,
        protected EntityRepository $shippingMethodRepository
    ) {
    }

    public static function buildKey(string $shippingMethodId): string
    {
        return 'shipping-method-' . $shippingMethodId;
    }

    public function collect(CartDataCollection $data, Cart $original, ChannelContext $context, CartBehavior $behavior): void
    {
        Profiler::trace('cart::delivery::collect', function () use ($data, $original, $context): void {
            $default = $context->getShippingMethod()->getId();

            if (!$data->has(self::buildKey($default))) {
                $ids = [$default];
            }

            foreach ($original->getDeliveries() as $delivery) {
                $id = $delivery->getShippingMethod()->getId();

                if (!$data->has(self::buildKey($id))) {
                    $ids[] = $id;
                }
            }

            if (empty($ids)) {
                return;
            }

            $criteria = (new Criteria($ids))
                ->addAssociations([
                    'prices',
                    'deliveryTime',
                    'tax',
                ])
                ->setTitle('cart::shipping-methods');

            $shippingMethods = $this->shippingMethodRepository->search($criteria, $context->getContext())->getEntities();

            foreach ($ids as $id) {
                $key = self::buildKey($id);

                if (!$shippingMethods->has($id)) {
                    continue;
                }

                $data->set($key, $shippingMethods->get($id));
            }
        }, 'cart');
    }

    public function process(CartDataCollection $data, Cart $original, Cart $toCalculate, ChannelContext $context, CartBehavior $behavior): void
    {
        Profiler::trace('cart::delivery::process', function () use ($data, $original, $toCalculate, $context, $behavior): void {
            if ($behavior->hasPermission(self::SKIP_DELIVERY_PRICE_RECALCULATION)) {
                $deliveries = $original->getDeliveries()->filter(function (Delivery $delivery) {
                    return $delivery->getShippingCosts()->getTotalPrice() >= 0;
                });

                $firstDelivery = $original->getDeliveries()->getPrimaryDelivery(
                    $original->getExtensionOfType(OrderConverter::ORIGINAL_PRIMARY_ORDER_DELIVERY, IdStruct::class)?->getId()
                );

                if (!Feature::isActive('v6.8.0.0')) {
                    $firstDelivery = $deliveries->first();
                }

                if ($firstDelivery === null) {
                    return;
                }

                // Stored original edit shipping cost
                $manualShippingCosts = $toCalculate->getExtension(self::MANUAL_SHIPPING_COSTS) ?? $firstDelivery->getShippingCosts();

                $toCalculate->addExtension(self::MANUAL_SHIPPING_COSTS, $manualShippingCosts);

                if ($manualShippingCosts instanceof CalculatedPrice) {
                    $firstDelivery->setShippingCosts($manualShippingCosts);
                }

                $this->deliveryCalculator->calculate($data, $toCalculate, $deliveries, $context);

                $toCalculate->setDeliveries($deliveries);

                return;
            }

            $deliveries = $this->builder->build($toCalculate, $data, $context, $behavior);
            $manualShippingCosts = $original->getExtension(self::MANUAL_SHIPPING_COSTS);

            if ($manualShippingCosts instanceof CalculatedPrice) {
                $deliveries->first()?->setShippingCosts($manualShippingCosts);
            }

            $this->deliveryCalculator->calculate($data, $toCalculate, $deliveries, $context);

            $toCalculate->setDeliveries($deliveries);
        }, 'cart');
    }
}
