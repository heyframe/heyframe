<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Search;

use HeyFrame\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use HeyFrame\Core\Content\Product\Channel\Listing\ProductListingLoader;
use HeyFrame\Core\Content\Product\Channel\Listing\ProductListingResult;
use HeyFrame\Core\Content\Product\Channel\ProductAvailableFilter;
use HeyFrame\Core\Content\Product\SearchKeyword\ProductSearchBuilderInterface;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\StoreApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StoreApiRouteScope::ID]])]
#[Package('inventory')]
class ProductSearchRoute extends AbstractProductSearchRoute
{
    /**
     * @internal
     */
    public function __construct(
        private readonly ProductSearchBuilderInterface $searchBuilder,
        private readonly ProductListingLoader $productListingLoader
    ) {
    }

    public function getDecorated(): AbstractProductSearchRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/store-api/search', name: 'store-api.search', methods: ['POST'], defaults: ['_entity' => 'product'])]
    public function load(Request $request, ChannelContext $context, Criteria $criteria): ProductSearchRouteResponse
    {
        $criteria->addState(Criteria::STATE_ELASTICSEARCH_AWARE);

        $criteria->addFilter(
            new ProductAvailableFilter($context->getChannelId(), ProductVisibilityDefinition::VISIBILITY_SEARCH)
        );

        if ($request->get('search')) {
            $this->searchBuilder->build($request, $criteria, $context);
        }

        $result = $this->productListingLoader->load($criteria, $context);

        $result = ProductListingResult::createFrom($result);

        return new ProductSearchRouteResponse($result);
    }
}
