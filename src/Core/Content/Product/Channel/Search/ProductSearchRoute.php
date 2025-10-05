<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Search;

use HeyFrame\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use HeyFrame\Core\Content\Product\Channel\Listing\ProductListingLoader;
use HeyFrame\Core\Content\Product\Channel\Listing\ProductListingResult;
use HeyFrame\Core\Content\Product\Channel\ProductAvailableFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\FrontApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [FrontApiRouteScope::ID]])]
#[Package('inventory')]
class ProductSearchRoute extends AbstractProductSearchRoute
{
    /**
     * @internal
     */
    public function __construct(
        private readonly ProductListingLoader $productListingLoader
    ) {
    }

    public function getDecorated(): AbstractProductSearchRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/front-api/search', name: 'front-api.search', defaults: ['_entity' => 'product'], methods: ['POST'])]
    public function load(Request $request, ChannelContext $context, Criteria $criteria): ProductSearchRouteResponse
    {
        $criteria->addFilter(
            new ProductAvailableFilter($context->getChannelId(), ProductVisibilityDefinition::VISIBILITY_SEARCH)
        );

        $result = $this->productListingLoader->load($criteria, $context);

        $result = ProductListingResult::createFrom($result);

        return new ProductSearchRouteResponse($result);
    }
}
