<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Suggest;

use HeyFrame\Core\Content\Product\Channel\Listing\ProductListingLoader;
use HeyFrame\Core\Content\Product\Channel\Listing\ProductListingResult;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\FrontApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [FrontApiRouteScope::ID]])]
#[Package('discovery')]
class ProductSuggestRoute extends AbstractProductSuggestRoute
{
    public const STATE = 'suggest-route-context';

    /**
     * @internal
     */
    public function __construct(
        private readonly ProductListingLoader $productListingLoader
    ) {
    }

    public function getDecorated(): AbstractProductSuggestRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/front-api/search-suggest', name: 'front-api.search.suggest', methods: ['POST'], defaults: ['_entity' => 'product'])]
    public function load(Request $request, ChannelContext $context, Criteria $criteria): ProductSuggestRouteResponse
    {
        $result = $this->productListingLoader->load($criteria, $context);

        $result = ProductListingResult::createFrom($result);

        return new ProductSuggestRouteResponse($result);
    }
}
