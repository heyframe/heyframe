<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Suggest;

use HeyFrame\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use HeyFrame\Core\Content\Product\Channel\Listing\Processor\CompositeListingProcessor;
use HeyFrame\Core\Content\Product\Channel\ProductAvailableFilter;
use HeyFrame\Core\Content\Product\Events\ProductSuggestCriteriaEvent;
use HeyFrame\Core\Content\Product\Events\ProductSuggestResultEvent;
use HeyFrame\Core\Content\Product\ProductEvents;
use HeyFrame\Core\Content\Product\ProductException;
use HeyFrame\Core\Content\Product\SearchKeyword\ProductSearchBuilderInterface;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Routing\StoreApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StoreApiRouteScope::ID]])]
#[Package('discovery')]
class ResolvedCriteriaProductSuggestRoute extends AbstractProductSuggestRoute
{
    /**
     * @internal
     */
    public function __construct(
        private readonly ProductSearchBuilderInterface $searchBuilder,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly AbstractProductSuggestRoute $decorated,
        private readonly CompositeListingProcessor $processor
    ) {
    }

    public function getDecorated(): AbstractProductSuggestRoute
    {
        return $this->decorated;
    }

    #[Route(
        path: '/store-api/search-suggest',
        name: 'store-api.search.suggest',
        defaults: ['_entity' => 'product'],
        methods: ['POST']
    )]
    public function load(Request $request, ChannelContext $context, Criteria $criteria): ProductSuggestRouteResponse
    {
        if (!$request->get('search')) {
            throw ProductException::missingRequestParameter('search');
        }

        $criteria->addState(ProductSuggestRoute::STATE);
        $criteria->addState(Criteria::STATE_ELASTICSEARCH_AWARE);

        $criteria->addFilter(
            new ProductAvailableFilter($context->getChannelId(), ProductVisibilityDefinition::VISIBILITY_SEARCH)
        );

        $this->searchBuilder->build($request, $criteria, $context);

        $this->processor->prepare($request, $criteria, $context);

        $this->eventDispatcher->dispatch(
            new ProductSuggestCriteriaEvent($request, $criteria, $context),
            ProductEvents::PRODUCT_SUGGEST_CRITERIA
        );

        $response = $this->getDecorated()->load($request, $context, $criteria);

        $this->processor->process($request, $response->getListingResult(), $context);

        $this->eventDispatcher->dispatch(
            new ProductSuggestResultEvent($request, $response->getListingResult(), $context),
            ProductEvents::PRODUCT_SUGGEST_RESULT
        );

        return $response;
    }
}
