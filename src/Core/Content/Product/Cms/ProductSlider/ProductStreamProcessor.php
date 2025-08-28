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
use HeyFrame\Core\Content\ProductStream\Service\ProductStreamBuilderInterface;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\NotEqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Grouping\FieldGrouping;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Entity\ChannelRepository;

#[Package('discovery')]
class ProductStreamProcessor extends AbstractProductSliderProcessor
{
    private const FALLBACK_LIMIT = 50;

    /**
     * @internal
     *
     * @param ChannelRepository<ProductCollection> $productRepository
     */
    public function __construct(
        private readonly ProductStreamBuilderInterface $productStreamBuilder,
        private readonly ChannelRepository $productRepository,
    ) {
    }

    public function getDecorated(): AbstractProductSliderProcessor
    {
        throw new DecorationPatternException(self::class);
    }

    public function getSource(): string
    {
        return 'product_stream';
    }

    public function collect(CmsSlotEntity $slot, FieldConfigCollection $config, ResolverContext $resolverContext): ?CriteriaCollection
    {
        $products = $config->get('products');
        \assert($products instanceof FieldConfig);
        $criteria = $this->collectByProductStream($resolverContext, $products, $config);

        $collection = new CriteriaCollection();
        $collection->add(self::PRODUCT_SLIDER_ENTITY_FALLBACK . '_' . $slot->getUniqueIdentifier(), ProductDefinition::class, $criteria);

        return $collection;
    }

    public function enrich(CmsSlotEntity $slot, ElementDataCollection $result, ResolverContext $resolverContext): void
    {
        $entitySearchResult = $result->get(self::PRODUCT_SLIDER_ENTITY_FALLBACK . '_' . $slot->getUniqueIdentifier());
        if (!$entitySearchResult) {
            return;
        }

        $streamResult = $entitySearchResult->getEntities();
        if (!$streamResult instanceof ProductCollection) {
            return;
        }

        $slider = new ProductSliderStruct();
        $slot->setData($slider);

        $slider->setProducts(
            $this->handleProductStream(
                $streamResult,
                $resolverContext->getChannelContext(),
                $entitySearchResult->getCriteria()
            )
        );

        $config = $slot->getFieldConfig();

        $productConfig = $config->get('products');
        \assert($productConfig instanceof FieldConfig);

        $slider->setStreamId($productConfig->getStringValue());
    }

    private function collectByProductStream(
        ResolverContext $resolverContext,
        FieldConfig $config,
        FieldConfigCollection $elementConfig
    ): Criteria {
        $filters = $this->productStreamBuilder->buildFilters(
            $config->getStringValue(),
            $resolverContext->getChannelContext()->getContext()
        );

        $limit = $elementConfig->get('productStreamLimit')?->getIntValue() ?? self::FALLBACK_LIMIT;

        $criteria = new Criteria();
        $criteria->addFilter(...$filters);
        $criteria->setLimit($limit);

        $this->addGrouping($criteria);
        $sorting = $elementConfig->get('productStreamSorting')?->getStringValue() ?? 'name:' . FieldSorting::ASCENDING;

        if ($sorting === 'random') {
            $this->addRandomSort($criteria);
        } else {
            $sorting = explode(':', $sorting);
            $field = $sorting[0];
            $direction = $sorting[1];

            $criteria->addSorting(new FieldSorting($field, $direction));
        }

        return $criteria;
    }

    private function handleProductStream(
        ProductCollection $streamResult,
        ChannelContext $context,
        Criteria $originCriteria
    ): ProductCollection {
        $finalProductIds = $this->collectFinalProductIds($streamResult);
        if (\count($finalProductIds) === 0) {
            return new ProductCollection();
        }

        $criteria = $originCriteria->cloneForRead($finalProductIds);

        $products = $this->productRepository->search($criteria, $context)->getEntities();
        $products->sortByIdArray($finalProductIds);

        return $products;
    }

    /**
     * @return list<string>
     */
    private function collectFinalProductIds(ProductCollection $streamResult): array
    {
        $finalProductIds = [];
        foreach ($streamResult as $product) {
            $variantConfig = $product->getVariantListingConfig();

            if (!$variantConfig) {
                $finalProductIds[] = $product->getId();
                continue;
            }

            $productId = $variantConfig->getDisplayParent()
                ? $product->getParentId() : $variantConfig->getMainVariantId();

            $finalProductIds[] = $productId ?? $product->getId();
        }

        return array_values(array_unique($finalProductIds));
    }

    private function addGrouping(Criteria $criteria): void
    {
        $criteria->addGroupField(new FieldGrouping('displayGroup'));
        $criteria->addFilter(new NotEqualsFilter('displayGroup', null));
    }

    private function addRandomSort(Criteria $criteria): void
    {
        $fields = [
            'id',
            'stock',
            'releaseDate',
            'manufacturer.id',
            'unit.id',
            'tax.id',
            'cover.id',
        ];
        shuffle($fields);
        $fields = \array_slice($fields, 0, 2);
        $direction = [FieldSorting::ASCENDING, FieldSorting::DESCENDING];
        $direction = $direction[random_int(0, 1)];

        foreach ($fields as $field) {
            $criteria->addSorting(new FieldSorting($field, $direction));
        }
    }
}
