<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Cms\ProductSlider;

use HeyFrame\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use HeyFrame\Core\Content\Cms\DataResolver\CriteriaCollection;
use HeyFrame\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use HeyFrame\Core\Content\Cms\DataResolver\FieldConfigCollection;
use HeyFrame\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use HeyFrame\Core\Content\Product\ProductCollection;
use HeyFrame\Core\Content\Product\ProductEntity;
use HeyFrame\Core\Framework\Log\Package;

#[Package('discovery')]
abstract class AbstractProductSliderProcessor
{
    protected const PRODUCT_SLIDER_ENTITY_FALLBACK = 'product-slider-entity-fallback';

    abstract public function getDecorated(): AbstractProductSliderProcessor;

    abstract public function getSource(): string;

    abstract public function collect(CmsSlotEntity $slot, FieldConfigCollection $config, ResolverContext $resolverContext): ?CriteriaCollection;

    abstract public function enrich(CmsSlotEntity $slot, ElementDataCollection $result, ResolverContext $resolverContext): void;

    protected function filterOutOutOfStockHiddenCloseoutProducts(ProductCollection $products): ProductCollection
    {
        return $products->filter(function (ProductEntity $product) {
            if ($product->getIsCloseout() && $product->getStock() <= 0) {
                return false;
            }

            return true;
        });
    }
}
