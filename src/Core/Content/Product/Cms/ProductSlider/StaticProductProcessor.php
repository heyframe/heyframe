<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Cms\ProductSlider;

use HeyFrame\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use HeyFrame\Core\Content\Cms\Channel\Struct\ProductSliderStruct;
use HeyFrame\Core\Content\Cms\DataResolver\CriteriaCollection;
use HeyFrame\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use HeyFrame\Core\Content\Cms\DataResolver\FieldConfig;
use HeyFrame\Core\Content\Cms\DataResolver\FieldConfigCollection;
use HeyFrame\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use HeyFrame\Core\Content\Product\ProductCollection;
use HeyFrame\Core\Content\Product\ProductDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;

#[Package('discovery')]
class StaticProductProcessor extends AbstractProductSliderProcessor
{
    private const STATIC_SEARCH_KEY = 'product-slider';

    /**
     * @internal
     */
    public function __construct(
        private readonly SystemConfigService $systemConfigService,
    ) {
    }

    public function getDecorated(): AbstractProductSliderProcessor
    {
        throw new DecorationPatternException(self::class);
    }

    public function getSource(): string
    {
        return 'static';
    }

    public function collect(CmsSlotEntity $slot, FieldConfigCollection $config, ResolverContext $resolverContext): ?CriteriaCollection
    {
        $products = $config->get('products');
        \assert($products instanceof FieldConfig);
        $criteria = new Criteria($products->getArrayValue());

        $collection = new CriteriaCollection();
        $collection->add(self::STATIC_SEARCH_KEY . '_' . $slot->getUniqueIdentifier(), ProductDefinition::class, $criteria);

        return $collection;
    }

    public function enrich(CmsSlotEntity $slot, ElementDataCollection $result, ResolverContext $resolverContext): void
    {
        $key = self::STATIC_SEARCH_KEY . '_' . $slot->getUniqueIdentifier();
        $searchResult = $result->get($key);

        if (!$searchResult) {
            return;
        }

        $products = $searchResult->getEntities();
        if (!$products instanceof ProductCollection) {
            return;
        }

        $context = $resolverContext->getChannelContext();

        if ($this->hideUnavailableProducts($context)) {
            $products = $this->filterOutOutOfStockHiddenCloseoutProducts($products);
        }

        $slider = new ProductSliderStruct();
        $slider->setProducts($products);

        $slot->setData($slider);
    }

    protected function hideUnavailableProducts(ChannelContext $context): bool
    {
        return (bool) $this->systemConfigService->get(
            'core.listing.hideCloseoutProductsWhenOutOfStock',
            $context->getChannelId()
        );
    }
}
