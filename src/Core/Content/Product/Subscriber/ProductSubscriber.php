<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Subscriber;

use HeyFrame\Core\Content\Product\AbstractProductMaxPurchaseCalculator;
use HeyFrame\Core\Content\Product\AbstractProductVariationBuilder;
use HeyFrame\Core\Content\Product\AbstractPropertyGroupSorter;
use HeyFrame\Core\Content\Product\Channel\Price\AbstractProductPriceCalculator;
use HeyFrame\Core\Content\Product\DataAbstractionLayer\CheapestPrice\CheapestPriceContainer;
use HeyFrame\Core\Content\Product\ProductDefinition;
use HeyFrame\Core\Content\Product\ProductEntity;
use HeyFrame\Core\Content\Product\ProductEvents;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use HeyFrame\Core\Framework\DataAbstractionLayer\PartialEntity;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\Entity\ChannelEntityLoadedEvent;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('inventory')]
class ProductSubscriber implements EventSubscriberInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly AbstractProductVariationBuilder $productVariationBuilder,
        private readonly AbstractProductPriceCalculator $calculator,
        private readonly AbstractPropertyGroupSorter $propertyGroupSorter,
        private readonly AbstractProductMaxPurchaseCalculator $maxPurchaseCalculator,
        private readonly SystemConfigService $systemConfigService,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductEvents::PRODUCT_LOADED_EVENT => 'loaded',
            'product.partial_loaded' => 'loaded',
            'channel.' . ProductEvents::PRODUCT_LOADED_EVENT => 'channelLoaded',
            'channel.product.partial_loaded' => 'channelLoaded',
        ];
    }

    /**
     * @param EntityLoadedEvent<ProductEntity|PartialEntity> $event
     */
    public function loaded(EntityLoadedEvent $event): void
    {
        foreach ($event->getEntities() as $product) {
            if (!$product instanceof ProductEntity && !$product instanceof PartialEntity) {
                continue;
            }

            $this->setDefaultLayout($product);

            $this->productVariationBuilder->build($product);
        }
    }

    /**
     * @param ChannelEntityLoadedEvent<ProductEntity|PartialEntity> $event
     */
    public function channelLoaded(ChannelEntityLoadedEvent $event): void
    {
        foreach ($event->getEntities() as $product) {
            $price = $product->get('cheapestPrice');

            if ($price instanceof CheapestPriceContainer) {
                $product->assign([
                    'cheapestPrice' => $price->resolve($event->getContext()),
                    'cheapestPriceContainer' => $price,
                ]);
            }

            $assigns = [];

            if (($properties = $product->get('properties')) !== null) {
                $assigns['sortedProperties'] = $this->propertyGroupSorter->sort($properties);
            }

            $assigns['calculatedMaxPurchase'] = $this->maxPurchaseCalculator->calculate($product, $event->getChannelContext());

            $product->assign($assigns);

            $this->setDefaultLayout($product, $event->getChannelContext()->getChannelId());

            $this->productVariationBuilder->build($product);
        }

        $this->calculator->calculate($event->getEntities(), $event->getChannelContext());
    }

    /**
     * @param Entity $product - typehint as Entity because it could be a ProductEntity or PartialEntity
     */
    private function setDefaultLayout(Entity $product, ?string $channelId = null): void
    {
        if (!$product->has('cmsPageId')) {
            return;
        }

        if ($product->get('cmsPageId') !== null) {
            return;
        }

        $cmsPageId = $this->systemConfigService->get(ProductDefinition::CONFIG_KEY_DEFAULT_CMS_PAGE_PRODUCT, $channelId);

        if (!$cmsPageId) {
            return;
        }

        $product->assign(['cmsPageId' => $cmsPageId]);
    }
}
