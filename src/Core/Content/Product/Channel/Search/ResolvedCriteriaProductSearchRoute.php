<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Search;

use HeyFrame\Core\Content\Product\Events\ProductSearchCriteriaEvent;
use HeyFrame\Core\Content\Product\Events\ProductSearchResultEvent;
use HeyFrame\Core\Content\Product\ProductEvents;
use HeyFrame\Core\Content\Product\Channel\Listing\Processor\CompositeListingProcessor;
use HeyFrame\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\RequestCriteriaBuilder;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Routing\FrontApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [FrontApiRouteScope::ID]])]
#[Package('inventory')]
class ResolvedCriteriaProductSearchRoute extends AbstractProductSearchRoute
{
    final public const DEFAULT_SEARCH_SORT = 'score';
    final public const STATE = 'search-route-context';

    /**
     * @internal
     */
    public function __construct(
        private readonly AbstractProductSearchRoute $decorated,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly DefinitionInstanceRegistry $registry,
        private readonly RequestCriteriaBuilder $criteriaBuilder,
        private readonly CompositeListingProcessor $processor
    ) {
    }

    public function getDecorated(): AbstractProductSearchRoute
    {
        return $this->decorated;
    }

    #[Route(path: '/store-api/search', name: 'store-api.search', methods: ['POST'], defaults: ['_entity' => 'product'])]
    public function load(Request $request, ChannelContext $context, Criteria $criteria): ProductSearchRouteResponse
    {
        $criteria->addState(self::STATE);

        $criteria = $this->criteriaBuilder->handleRequest(
            $request,
            $criteria,
            $this->registry->getByEntityName('product'),
            $context->getContext()
        );

        // will be handled via processor in next line
        $criteria->setLimit(null);

        $this->processor->prepare($request, $criteria, $context);

        $this->eventDispatcher->dispatch(
            new ProductSearchCriteriaEvent($request, $criteria, $context),
            ProductEvents::PRODUCT_SEARCH_CRITERIA
        );

        $response = $this->getDecorated()->load($request, $context, $criteria);

        $this->processor->process($request, $response->getListingResult(), $context);

        $this->eventDispatcher->dispatch(
            new ProductSearchResultEvent($request, $response->getListingResult(), $context),
            ProductEvents::PRODUCT_SEARCH_RESULT
        );

        $response->getListingResult()->addCurrentFilter('search', $request->get('search'));

        return $response;
    }
}
