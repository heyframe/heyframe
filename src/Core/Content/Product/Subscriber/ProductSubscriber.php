<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Subscriber;

use HeyFrame\Core\Content\MeasurementSystem\MeasurementUnits;
use HeyFrame\Core\Content\MeasurementSystem\MeasurementUnitTypeEnum;
use HeyFrame\Core\Content\MeasurementSystem\ProductMeasurement\ProductMeasurementEnum;
use HeyFrame\Core\Content\MeasurementSystem\ProductMeasurement\ProductMeasurementUnitBuilder;
use HeyFrame\Core\Content\MeasurementSystem\Unit\AbstractMeasurementUnitConverter;
use HeyFrame\Core\Content\Product\AbstractIsNewDetector;
use HeyFrame\Core\Content\Product\AbstractProductMaxPurchaseCalculator;
use HeyFrame\Core\Content\Product\AbstractProductVariationBuilder;
use HeyFrame\Core\Content\Product\AbstractPropertyGroupSorter;
use HeyFrame\Core\Content\Product\Channel\Price\AbstractProductPriceCalculator;
use HeyFrame\Core\Content\Product\DataAbstractionLayer\CheapestPrice\CheapestPriceContainer;
use HeyFrame\Core\Content\Product\ProductDefinition;
use HeyFrame\Core\Content\Product\ProductEntity;
use HeyFrame\Core\Content\Product\ProductEvents;
use HeyFrame\Core\Framework\Api\Context\AdminApiSource;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use HeyFrame\Core\Framework\DataAbstractionLayer\Event\EntityWriteEvent;
use HeyFrame\Core\Framework\DataAbstractionLayer\PartialEntity;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\Entity\ChannelEntityLoadedEvent;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

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
        private readonly AbstractIsNewDetector $isNewDetector,
        private readonly SystemConfigService $systemConfigService,
        private readonly ProductMeasurementUnitBuilder $measurementUnitBuilder,
        private readonly AbstractMeasurementUnitConverter $measurementUnitConverter,
        private readonly RequestStack $requestStack
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductEvents::PRODUCT_LOADED_EVENT => 'loaded',
            'product.partial_loaded' => 'loaded',
            'channel.' . ProductEvents::PRODUCT_LOADED_EVENT => 'channelLoaded',
            'channel.product.partial_loaded' => 'channelLoaded',
            EntityWriteEvent::class => 'beforeWriteProduct',
        ];
    }

    /**
     * @param EntityLoadedEvent<ProductEntity|PartialEntity> $event
     */
    public function loaded(EntityLoadedEvent $event): void
    {
        $isAdminSource = $event->getContext()->getSource() instanceof AdminApiSource;

        foreach ($event->getEntities() as $product) {
            if (!$product instanceof ProductEntity && !$product instanceof PartialEntity) {
                continue;
            }

            if ($isAdminSource) {
                $this->convertMeasurementUnit($product);
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

            $assigns['isNew'] = $this->isNewDetector->isNew($product, $event->getChannelContext());

            $assigns['measurements'] = $this->measurementUnitBuilder->buildFromContext($product, $event->getChannelContext());

            $product->assign($assigns);

            $this->setDefaultLayout($product, $event->getChannelContext()->getChannelId());

            $this->productVariationBuilder->build($product);
        }

        $this->calculator->calculate($event->getEntities(), $event->getChannelContext());
    }

    public function beforeWriteProduct(EntityWriteEvent $event): void
    {
        $lengthUnitHeader = $this->requestStack->getCurrentRequest()?->headers->get(PlatformRequest::HEADER_MEASUREMENT_LENGTH_UNIT);
        $weightUnitHeader = $this->requestStack->getCurrentRequest()?->headers->get(PlatformRequest::HEADER_MEASUREMENT_WEIGHT_UNIT);

        if (!$lengthUnitHeader && !$weightUnitHeader) {
            return;
        }

        $commands = $event->getCommandsForEntity(ProductDefinition::ENTITY_NAME);

        foreach ($commands as $command) {
            $payload = $command->getPayload();

            foreach (ProductMeasurementEnum::DIMENSIONS_MAPPING as $dimension => $type) {
                if (!$command->hasField($dimension) || !\is_float($payload[$dimension] ?? null)) {
                    continue;
                }

                $fromUnit = $type === MeasurementUnitTypeEnum::WEIGHT
                    ? $weightUnitHeader
                    : $lengthUnitHeader;

                $toUnit = $type === MeasurementUnitTypeEnum::WEIGHT
                    ? MeasurementUnits::DEFAULT_WEIGHT_UNIT
                    : MeasurementUnits::DEFAULT_LENGTH_UNIT;

                if ($fromUnit) {
                    $command->addPayload($dimension, $this->measurementUnitConverter->convert(
                        $payload[$dimension],
                        $fromUnit,
                        $toUnit,
                    )->value);
                }
            }
        }
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

    private function convertMeasurementUnit(ProductEntity|PartialEntity $product): void
    {
        $lengthUnitHeader = $this->requestStack->getCurrentRequest()?->headers->get(PlatformRequest::HEADER_MEASUREMENT_LENGTH_UNIT);
        $weightUnitHeader = $this->requestStack->getCurrentRequest()?->headers->get(PlatformRequest::HEADER_MEASUREMENT_WEIGHT_UNIT);

        if (!$lengthUnitHeader && !$weightUnitHeader) {
            return;
        }

        $toLengthUnit = $lengthUnitHeader ?? MeasurementUnits::DEFAULT_LENGTH_UNIT;
        $toWeightUnit = $weightUnitHeader ?? MeasurementUnits::DEFAULT_WEIGHT_UNIT;

        $converted = $this->measurementUnitBuilder->build($product, $toLengthUnit, $toWeightUnit);

        $assigns = [];

        foreach ($converted->getUnits() as $unit => $convertedUnit) {
            $assigns[$unit] = $convertedUnit->value;
        }

        if (!empty($assigns)) {
            $product->assign($assigns);
        }
    }
}
