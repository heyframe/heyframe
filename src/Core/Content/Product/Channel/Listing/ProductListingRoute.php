<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Listing;

use HeyFrame\Core\Content\Category\CategoryCollection;
use HeyFrame\Core\Content\Category\CategoryDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use HeyFrame\Core\Content\Product\Channel\ProductAvailableFilter;
use HeyFrame\Core\Content\Product\Extension\ProductListingCriteriaExtension;
use HeyFrame\Core\Content\Product\ProductException;
use HeyFrame\Core\Content\ProductStream\Service\ProductStreamBuilderInterface;
use HeyFrame\Core\Framework\Adapter\Cache\CacheTagCollector;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\PartialEntity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Extensions\ExtensionDispatcher;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\FrontApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [FrontApiRouteScope::ID]])]
#[Package('inventory')]
class ProductListingRoute extends AbstractProductListingRoute
{
    /**
     * @internal
     *
     * @param EntityRepository<CategoryCollection> $categoryRepository
     */
    public function __construct(
        private readonly ProductListingLoader $listingLoader,
        private readonly EntityRepository $categoryRepository,
        private readonly ProductStreamBuilderInterface $productStreamBuilder,
        private readonly CacheTagCollector $cacheTagCollector,
        private readonly ExtensionDispatcher $extensions,
    ) {
    }

    public function getDecorated(): AbstractProductListingRoute
    {
        throw new DecorationPatternException(self::class);
    }

    public static function buildName(string $categoryId): string
    {
        return 'product-listing-' . $categoryId;
    }

    #[Route(path: '/front-api/product-listing/{categoryId}', name: 'front-api.product.listing', defaults: ['_entity' => 'product'], methods: ['POST'])]
    public function load(string $categoryId, Request $request, ChannelContext $context, Criteria $criteria): ProductListingRouteResponse
    {
        $this->cacheTagCollector->addTag(self::buildName($categoryId));

        $criteria->addFilter(
            new ProductAvailableFilter($context->getChannelId(), ProductVisibilityDefinition::VISIBILITY_ALL)
        );
        $criteria->setTitle('product-listing-route::loading');

        $categoryCriteria = new Criteria([$categoryId]);
        $categoryCriteria->setTitle('product-listing-route::category-loading');
        $categoryCriteria->addFields(['productAssignmentType', 'productStreamId']);
        $categoryCriteria->setLimit(1);

        /** @var ?PartialEntity */
        $category = $this->categoryRepository->search($categoryCriteria, $context->getContext())->getEntities()->first();
        if (!$category) {
            throw ProductException::categoryNotFound($categoryId);
        }

        $criteria = $this->extensions->publish(
            name: ProductListingCriteriaExtension::NAME,
            extension: new ProductListingCriteriaExtension($criteria, $context, $categoryId),
            function: function ($criteria, $context, $categoryId) use ($category): Criteria {
                $this->extendCriteria($context, $criteria, $category);

                return $criteria;
            }
        );

        $entities = $this->listingLoader->load($criteria, $context);

        $result = ProductListingResult::createFrom($entities);
        $result->addState(...$entities->getStates());

        $result->setStreamId($category->get('productStreamId'));

        return new ProductListingRouteResponse($result);
    }

    private function extendCriteria(ChannelContext $channelContext, Criteria $criteria, PartialEntity $category): void
    {
        $hasProductStream = $category->get('productAssignmentType') === CategoryDefinition::PRODUCT_ASSIGNMENT_TYPE_PRODUCT_STREAM
            && $category->get('productStreamId') !== null;

        if ($hasProductStream) {
            $filters = $this->productStreamBuilder->buildFilters(
                $category->get('productStreamId'),
                $channelContext->getContext()
            );
            $criteria->addFilter(...$filters);

            return;
        }

        $criteria->addFilter(
            new EqualsFilter('product.categoriesRo.id', $category->getId())
        );
    }
}
