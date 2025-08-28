<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\CrossSelling;

use HeyFrame\Core\Content\Product\Aggregate\ProductCrossSelling\ProductCrossSellingCollection;
use HeyFrame\Core\Content\Product\Aggregate\ProductCrossSelling\ProductCrossSellingDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductCrossSelling\ProductCrossSellingEntity;
use HeyFrame\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use HeyFrame\Core\Content\Product\Channel\AbstractProductCloseoutFilterFactory;
use HeyFrame\Core\Content\Product\Channel\Listing\ProductListingLoader;
use HeyFrame\Core\Content\Product\Channel\ProductAvailableFilter;
use HeyFrame\Core\Content\Product\Events\ProductCrossSellingCriteriaLoadEvent;
use HeyFrame\Core\Content\Product\Events\ProductCrossSellingIdsCriteriaEvent;
use HeyFrame\Core\Content\Product\Events\ProductCrossSellingsLoadedEvent;
use HeyFrame\Core\Content\Product\Events\ProductCrossSellingStreamCriteriaEvent;
use HeyFrame\Core\Content\Product\ProductCollection;
use HeyFrame\Core\Content\ProductStream\Service\ProductStreamBuilderInterface;
use HeyFrame\Core\Framework\Adapter\Cache\CacheTagCollector;
use HeyFrame\Core\Framework\DataAbstractionLayer\Cache\EntityCacheKeyGenerator;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\NotEqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\StoreApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Entity\ChannelRepository;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StoreApiRouteScope::ID]])]
#[Package('inventory')]
class ProductCrossSellingRoute extends AbstractProductCrossSellingRoute
{
    /**
     * @internal
     *
     * @param EntityRepository<ProductCrossSellingCollection> $crossSellingRepository
     * @param ChannelRepository<ProductCollection> $productRepository
     */
    public function __construct(
        private readonly EntityRepository $crossSellingRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ProductStreamBuilderInterface $productStreamBuilder,
        private readonly ChannelRepository $productRepository,
        private readonly SystemConfigService $systemConfigService,
        private readonly ProductListingLoader $listingLoader,
        private readonly AbstractProductCloseoutFilterFactory $productCloseoutFilterFactory,
        private readonly CacheTagCollector $cacheTagCollector,
    ) {
    }

    public function getDecorated(): AbstractProductCrossSellingRoute
    {
        throw new DecorationPatternException(self::class);
    }

    public static function buildName(string $id): string
    {
        return EntityCacheKeyGenerator::buildProductTag($id);
    }

    #[Route(
        path: '/store-api/product/{productId}/cross-selling',
        name: 'store-api.product.cross-selling',
        defaults: ['_entity' => 'product'],
        methods: ['POST']
    )]
    public function load(string $productId, Request $request, ChannelContext $context, Criteria $criteria): ProductCrossSellingRouteResponse
    {
        $this->cacheTagCollector->addTag(self::buildName($productId));

        $crossSellings = $this->loadCrossSellings($productId, $context);

        $elements = new CrossSellingElementCollection();

        foreach ($crossSellings as $crossSelling) {
            $clone = clone $criteria;
            if ($this->useProductStream($crossSelling)) {
                $element = $this->loadByStream($crossSelling, $context, $clone);
            } else {
                $element = $this->loadByIds($crossSelling, $context, $clone);
            }

            $elements->add($element);
        }

        $this->eventDispatcher->dispatch(new ProductCrossSellingsLoadedEvent($elements, $context));

        return new ProductCrossSellingRouteResponse($elements);
    }

    private function loadCrossSellings(string $productId, ChannelContext $context): ProductCrossSellingCollection
    {
        $criteria = new Criteria();
        $criteria
            ->setTitle('product-cross-selling-route')
            ->addAssociation('assignedProducts')
            ->addFilter(new EqualsFilter('product.id', $productId))
            ->addFilter(new EqualsFilter('active', 1))
            ->addSorting(new FieldSorting('position', FieldSorting::ASCENDING));

        $this->eventDispatcher->dispatch(
            new ProductCrossSellingCriteriaLoadEvent($criteria, $context)
        );

        return $this->crossSellingRepository->search($criteria, $context->getContext())->getEntities();
    }

    private function loadByStream(ProductCrossSellingEntity $crossSelling, ChannelContext $context, Criteria $criteria): CrossSellingElement
    {
        $productStreamId = $crossSelling->getProductStreamId();
        \assert(\is_string($productStreamId));

        $filters = $this->productStreamBuilder->buildFilters($productStreamId, $context->getContext());

        $criteria->addFilter(...$filters)
            ->addFilter(new NotEqualsFilter('product.id', $crossSelling->getProductId()))
            ->setOffset(0)
            ->setLimit($crossSelling->getLimit())
            ->addSorting($crossSelling->getSorting());

        $criteria = $this->handleAvailableStock($criteria, $context);

        $this->eventDispatcher->dispatch(
            new ProductCrossSellingStreamCriteriaEvent($crossSelling, $criteria, $context)
        );

        $products = $this->listingLoader->load($criteria, $context)->getEntities();

        $element = new CrossSellingElement();
        $element->setCrossSelling($crossSelling);
        $element->setProducts($products);
        $element->setStreamId($crossSelling->getProductStreamId());

        $element->setTotal($products->count());

        return $element;
    }

    private function loadByIds(ProductCrossSellingEntity $crossSelling, ChannelContext $context, Criteria $criteria): CrossSellingElement
    {
        $element = new CrossSellingElement();
        $element->setCrossSelling($crossSelling);
        $element->setProducts(new ProductCollection());
        $element->setTotal(0);

        if (!$crossSelling->getAssignedProducts()) {
            return $element;
        }

        $crossSelling->getAssignedProducts()->sortByPosition();

        $ids = array_values($crossSelling->getAssignedProducts()->getProductIds());

        $filter = new ProductAvailableFilter(
            $context->getChannelId(),
            ProductVisibilityDefinition::VISIBILITY_LINK
        );

        if (!\count($ids)) {
            return $element;
        }

        $criteria->setIds($ids);
        $criteria->addFilter($filter);
        $criteria->addAssociation('options.group');

        $criteria = $this->handleAvailableStock($criteria, $context);

        $this->eventDispatcher->dispatch(
            new ProductCrossSellingIdsCriteriaEvent($crossSelling, $criteria, $context)
        );

        $products = $this->productRepository->search($criteria, $context)->getEntities();

        $ids = $criteria->getIds();
        $products->sortByIdArray($ids);

        $element->setProducts($products);
        $element->setTotal(\count($products));

        return $element;
    }

    private function handleAvailableStock(Criteria $criteria, ChannelContext $context): Criteria
    {
        $channelId = $context->getChannelId();
        $hide = $this->systemConfigService->get('core.listing.hideCloseoutProductsWhenOutOfStock', $channelId);

        if (!$hide) {
            return $criteria;
        }

        $closeoutFilter = $this->productCloseoutFilterFactory->create($context);
        $criteria->addFilter($closeoutFilter);

        return $criteria;
    }

    private function useProductStream(ProductCrossSellingEntity $crossSelling): bool
    {
        return $crossSelling->getType() === ProductCrossSellingDefinition::TYPE_PRODUCT_STREAM
            && $crossSelling->getProductStreamId() !== null;
    }
}
