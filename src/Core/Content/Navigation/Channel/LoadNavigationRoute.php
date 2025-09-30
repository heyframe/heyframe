<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Navigation\Channel;

use HeyFrame\Core\Content\Navigation\NavigationCollection;
use HeyFrame\Core\Content\Navigation\NavigationEntity;
use HeyFrame\Core\Content\Navigation\NavigationException;
use HeyFrame\Core\Framework\Adapter\Cache\CacheTagCollector;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\FrontApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Entity\ChannelRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [FrontApiRouteScope::ID]])]
#[Package('discovery')]
class LoadNavigationRoute extends AbstractLoadNavigationRoute
{
    final public const HOME = 'home';

    /**
     * @param ChannelRepository<NavigationCollection> $navigationRepository
     *
     * @internal
     */
    public function __construct(
        private readonly ChannelRepository $navigationRepository,
        private readonly CacheTagCollector $cacheTagCollector,
    ) {
    }

    public static function buildName(string $id): string
    {
        return 'navigation-route-' . $id;
    }

    public function getDecorated(): AbstractLoadNavigationRoute
    {
        throw new DecorationPatternException(self::class);
    }

    public function load(string $navigationId, Request $request, ChannelContext $context): LoadNavigationRouteResponse
    {
        $this->cacheTagCollector->addTag(self::buildName($navigationId));
        if ($navigationId === self::HOME) {
            $navigationId = $context->getChannel()->getNavigationId();
            $request->attributes->set('navigationId', $navigationId);

            $routeParams = $request->attributes->get('_route_params', []);
            $routeParams['navigationId'] = $navigationId;
            $request->attributes->set('_route_params', $routeParams);
        }

        $navigation = $this->loadNavigation($navigationId, $context);

        return new LoadNavigationRouteResponse($navigation);
    }

    private function loadNavigation(string $navigationId, ChannelContext $context): NavigationEntity
    {
        $criteria = new Criteria([$navigationId]);
        $criteria->setTitle('navigation::data');

        $criteria->addAssociation('media');
        $criteria->addAssociation('translations');

        $category = $this->navigationRepository->search($criteria, $context)->getEntities()->get($navigationId);
        if (!$category instanceof NavigationEntity) {
            throw NavigationException::navigationNotFound($navigationId);
        }

        return $category;
    }
}
