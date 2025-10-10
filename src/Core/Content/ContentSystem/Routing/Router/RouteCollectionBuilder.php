<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Routing\Router;

use HeyFrame\Core\Content\ContentSystem\Routing\Entity\ContentRouteCollection;
use HeyFrame\Core\Content\ContentSystem\Routing\Entity\ContentRouteEntity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * @final
 */
#[Package('discovery')]
class RouteCollectionBuilder
{
    /**
     * @internal
     *
     * @param EntityRepository<ContentRouteCollection> $contentRouteRepository
     */
    public function __construct(
        protected readonly EntityRepository $contentRouteRepository
    ) {
    }

    public function build(ChannelContext $context): RouteCollection
    {
        $channelId = $context->getChannel()->getId();
        $collection = new RouteCollection();

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('active', true));
        $criteria->addAssociation('channels');
        $criteria->addSorting(new FieldSorting('priority', FieldSorting::DESCENDING));

        $routes = $this->contentRouteRepository->search($criteria, $context->getContext());

        /** @var ContentRouteEntity $contentRoute */
        foreach ($routes as $contentRoute) {
            $channels = $contentRoute->getChannels();

            if ($channels === null || $channels->count() === 0 || $channels->has($channelId)) {
                $route = new Route($contentRoute->getUrlPattern());
                $route->setDefault('_content_route_id', $contentRoute->getId());
                $route->setDefault('_content_route', $contentRoute);

                $collection->add('content_route_' . $contentRoute->getId(), $route);
            }
        }

        return $collection;
    }
}
