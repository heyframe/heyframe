<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Listing;

use HeyFrame\Core\Content\Product\Events\ProductListingCriteriaEvent;
use HeyFrame\Core\Content\Product\Events\ProductListingResultEvent;
use HeyFrame\Core\Content\Product\Channel\Listing\Processor\CompositeListingProcessor;
use HeyFrame\Core\Content\Product\Channel\Search\ResolvedCriteriaProductSearchRoute;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Routing\FrontApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [FrontApiRouteScope::ID]])]
#[Package('inventory')]
class ResolveCriteriaProductListingRoute extends AbstractProductListingRoute
{
    public const STATE = 'listing-route-context';

    /**
     * @internal
     */
    public function __construct(
        private readonly AbstractProductListingRoute $decorated,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly CompositeListingProcessor $processor
    ) {
    }

    public function getDecorated(): AbstractProductListingRoute
    {
        return $this->decorated;
    }

    #[Route(path: '/store-api/product-listing/{categoryId}', name: 'store-api.product.listing', methods: ['POST'], defaults: ['_entity' => 'product'])]
    public function load(string $categoryId, Request $request, ChannelContext $context, Criteria $criteria): ProductListingRouteResponse
    {
        $criteria->addState(self::STATE);

        $this->processor->prepare($request, $criteria, $context);

        $this->eventDispatcher->dispatch(
            new ProductListingCriteriaEvent($request, $criteria, $context)
        );

        $response = $this->getDecorated()->load($categoryId, $request, $context, $criteria);

        $response->getResult()->addCurrentFilter('navigationId', $categoryId);

        $this->processor->process($request, $response->getResult(), $context);

        $this->eventDispatcher->dispatch(
            new ProductListingResultEvent($request, $response->getResult(), $context)
        );

        $response->getResult()->getAvailableSortings()->removeByKey(
            ResolvedCriteriaProductSearchRoute::DEFAULT_SEARCH_SORT
        );

        return $response;
    }
}
