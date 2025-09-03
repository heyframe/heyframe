<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Detail;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use HeyFrame\Core\Content\Product\Channel\AbstractProductCloseoutFilterFactory;
use HeyFrame\Core\Content\Product\Channel\ChannelProductCollection;
use HeyFrame\Core\Content\Product\Channel\ChannelProductDefinition;
use HeyFrame\Core\Content\Product\Channel\ChannelProductEntity;
use HeyFrame\Core\Content\Product\Channel\Detail\Event\ResolveVariantIdEvent;
use HeyFrame\Core\Content\Product\Channel\ProductAvailableFilter;
use HeyFrame\Core\Content\Product\ProductException;
use HeyFrame\Core\Framework\Adapter\Cache\CacheTagCollector;
use HeyFrame\Core\Framework\DataAbstractionLayer\Cache\EntityCacheKeyGenerator;
use HeyFrame\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\FrontApiRouteScope;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\Profiling\Profiler;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Entity\ChannelRepository;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [FrontApiRouteScope::ID]])]
#[Package('inventory')]
class ProductDetailRoute extends AbstractProductDetailRoute
{
    private const SKIP_CONFIGURATOR = 'skipConfigurator';
    private const SKIP_CMS_PAGE = 'skipCmsPage';

    /**
     * @param ChannelRepository<ChannelProductCollection> $productRepository
     *
     * @internal
     */
    public function __construct(
        private readonly ChannelRepository $productRepository,
        private readonly SystemConfigService $config,
        private readonly Connection $connection,
        private readonly ProductConfiguratorLoader $configuratorLoader,
        private readonly ChannelProductDefinition $productDefinition,
        private readonly AbstractProductCloseoutFilterFactory $productCloseoutFilterFactory,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly CacheTagCollector $cacheTagCollector,
    ) {
    }

    public static function buildName(string $parentId): string
    {
        return EntityCacheKeyGenerator::buildProductTag($parentId);
    }

    public function getDecorated(): AbstractProductDetailRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/front-api/product/{productId}', name: 'front-api.product.detail', methods: ['POST'], defaults: ['_entity' => 'product'])]
    public function load(string $productId, Request $request, ChannelContext $context, Criteria $criteria): ProductDetailRouteResponse
    {
        return Profiler::trace('product-detail-route', function () use ($productId, $request, $context, $criteria) {
            $mainVariantId = $this->checkVariantListingConfig($productId, $context);

            $resolveVariantIdEvent = new ResolveVariantIdEvent(
                $productId,
                $mainVariantId,
                $context,
            );

            $this->dispatcher->dispatch($resolveVariantIdEvent);

            if ($resolveVariantIdEvent->getResolvedVariantId()) {
                $productId = $resolveVariantIdEvent->getResolvedVariantId();
            } else {
                $term = $request->query->get('search');
                $variantId = $term ? $this->findBestVariantByTerm($term, $productId, $context) : null;
                $productId = $variantId ?? $this->findBestVariant($productId, $context);
            }

            $this->addFilters($context, $criteria);

            $criteria->setIds([$productId]);
            $criteria->setTitle('product-detail-route');

            $product = $this->productRepository->search($criteria, $context)->getEntities()->first();
            if (!($product instanceof ChannelProductEntity)) {
                throw ProductException::productNotFound($productId);
            }

            $parent = $product->getParentId() ?? $product->getId();

            $this->cacheTagCollector->addTag(EntityCacheKeyGenerator::buildProductTag($parent));

            $configurator = $this->configuratorLoader->load($product, $context);

            $pageId = $product->getCmsPageId();

            if ($pageId) {
                // clone product to prevent recursion encoding (see NEXT-17603)
                $resolverContext = new EntityResolverContext($context, $request, $this->productDefinition, clone $product);

                $pages = $this->cmsPageLoader->load(
                    $request,
                    $this->createCriteria($pageId, $request),
                    $context,
                    $product->getTranslation('slotConfig'),
                    $resolverContext
                );

                $cmsPage = $pages->first();
                if ($cmsPage !== null) {
                    $product->setCmsPage($cmsPage);
                }
            }

            return new ProductDetailRouteResponse($product, $configurator);
        });
    }

    private function addFilters(ChannelContext $context, Criteria $criteria): void
    {
        $criteria->addFilter(
            new ProductAvailableFilter($context->getChannelId(), ProductVisibilityDefinition::VISIBILITY_LINK)
        );

        $channelId = $context->getChannelId();

        $hideCloseoutProductsWhenOutOfStock = $this->config->get('core.listing.hideCloseoutProductsWhenOutOfStock', $channelId);

        if ($hideCloseoutProductsWhenOutOfStock) {
            $filter = $this->productCloseoutFilterFactory->create($context);
            $filter->addQuery(new EqualsFilter('product.parentId', null));
            $criteria->addFilter($filter);
        }
    }

    private function checkVariantListingConfig(string $productId, ChannelContext $context): ?string
    {
        if (!Uuid::isValid($productId)) {
            return null;
        }

        $productData = $this->connection->fetchAssociative(
            '# product-detail-route::check-variant-listing-config
            SELECT
                variant_listing_config as variantListingConfig,
                parent_id as parentId
            FROM product
            WHERE id = :id
            AND version_id = :versionId',
            [
                'id' => Uuid::fromHexToBytes($productId),
                'versionId' => Uuid::fromHexToBytes($context->getVersionId()),
            ]
        );

        if (empty($productData) || $productData['variantListingConfig'] === null) {
            return null;
        }

        $variantListingConfig = json_decode((string) $productData['variantListingConfig'], true, 512, \JSON_THROW_ON_ERROR);

        if (isset($variantListingConfig['displayParent']) && (bool) $variantListingConfig['displayParent'] === true) {
            return null;
        }

        return $variantListingConfig['mainVariantId'] ?? null;
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    private function findBestVariant(string $productId, ChannelContext $context): string
    {
        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('product.parentId', $productId))
            ->addSorting(new FieldSorting('product.available', FieldSorting::DESCENDING))
            ->addSorting(new FieldSorting('product.price'))
            ->setLimit(1);

        $criteria->setTitle('product-detail-route::find-best-variant');
        $variantId = $this->productRepository->searchIds($criteria, $context);

        return $variantId->firstId() ?? $productId;
    }

    private function findBestVariantByTerm(string $term, string $productId, ChannelContext $context): ?string
    {
        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('product.parentId', $productId))
            ->setLimit(1);

        $criteria->addState(Criteria::STATE_ELASTICSEARCH_AWARE);
        $criteria->setTerm($term);

        $criteria->setTitle('product-detail-route::find-best-variant-by-term');
        $variantId = $this->productRepository->searchIds($criteria, $context);

        return $variantId->firstId();
    }

    private function createCriteria(string $pageId, Request $request): Criteria
    {
        $criteria = new Criteria([$pageId]);
        $criteria->setTitle('product::cms-page');

        $slots = $request->get('slots');

        if (\is_string($slots)) {
            $slots = explode('|', $slots);
        }

        if (!empty($slots) && \is_array($slots)) {
            $criteria
                ->getAssociation('sections.blocks')
                ->addFilter(new EqualsAnyFilter('slots.id', $slots));
        }

        return $criteria;
    }
}
