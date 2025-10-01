<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Navigation\Channel;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Content\Navigation\NavigationCollection;
use HeyFrame\Core\Content\Navigation\NavigationException;
use HeyFrame\Core\Content\Navigation\Service\DefaultNavigationLevelLoaderInterface;
use HeyFrame\Core\Content\Navigation\Tree\NavigationTreePathResolver;
use HeyFrame\Core\Framework\Adapter\Cache\CacheTagCollector;
use HeyFrame\Core\Framework\DataAbstractionLayer\Doctrine\FetchModeHelper;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use HeyFrame\Core\Framework\Feature;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\FrontApiRouteScope;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Entity\ChannelRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @phpstan-type NavigationMetaInformation array{id: string, level: string, path: string}
 */
#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [FrontApiRouteScope::ID]])]
#[Package('discovery')]
class NavigationRoute extends AbstractNavigationRoute
{
    final public const ALL_TAG = 'navigation';

    /**
     * @internal
     *
     * @param ChannelRepository<NavigationCollection> $navigationRepository
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly ChannelRepository $navigationRepository,
        private readonly CacheTagCollector $cacheTagCollector,
        private readonly NavigationTreePathResolver $navigationTreePathResolver,
        private readonly DefaultNavigationLevelLoaderInterface $navigationLevelLoader,
    ) {
    }

    /**
     * @deprecated - tag:v6.8.0 - will be removed, navigation route will only be tagged globally, use NavigationRoute::ALL_TAG instead
     */
    public static function buildName(string $id): string
    {
        Feature::triggerDeprecationOrThrow(
            'v6.8.0.0',
            Feature::deprecatedMethodMessage(self::class, __METHOD__, 'v6.8.0.0', ' NavigationRoute::ALL_TAG')
        );

        return 'navigation-route-' . $id;
    }

    public function getDecorated(): AbstractNavigationRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/front-api/navigation/{activeId}/{rootId}', name: 'front-api.navigation', defaults: ['_entity' => 'navigation'], methods: ['GET', 'POST'])]
    public function load(
        string $activeId,
        string $rootId,
        Request $request,
        ChannelContext $context,
        Criteria $criteria
    ): NavigationRouteResponse {
        $depth = $request->query->getInt('depth', $request->request->getInt('depth', 2));

        $metaInfo = $this->getNavigationMetaInfo($activeId, $rootId);

        $active = $this->getMetaInfoById($activeId, $metaInfo);

        $tags = [self::ALL_TAG];

        // Navigation route will be tagged & invalidated globally only in 6.8
        Feature::callSilentIfInactive(
            'v6.8.0.0',
            static function () use ($context, $activeId, &$tags): void {
                $tags[] = self::buildName($context->getChannelId());
                $tags[] = self::buildName($activeId);
            }
        );

        $this->cacheTagCollector->addTag(...$tags);

        $root = $this->getMetaInfoById($rootId, $metaInfo);

        // Validate the provided navigation is part of the sales channel
        $this->validate($activeId, $active['path'], $context);

        $isChild = $this->isChildNavigation($activeId, $active['path'], $rootId);

        $activePath = $active['path'];
        // If the provided activeId is not part of the rootId, a fallback to the rootId must be made here.
        // The passed activeId is therefore part of another navigation and must therefore not be loaded.
        // The availability validation has already been done in the `validate` function.
        if (!$isChild) {
            $activeId = $rootId;
            $activePath = $root['path'];
        }

        $categories = $this->navigationLevelLoader->loadLevels(
            $rootId,
            (int) $root['level'],
            $context,
            clone $criteria,
            $depth
        );

        $additionalPathsToLoad = $this->navigationTreePathResolver->getAdditionalPathsToLoad($activeId, $activePath, $rootId, $root['path'], $depth);

        if ($additionalPathsToLoad !== []) {
            $categories->merge($this->loadAdditionalPaths($context, clone $criteria, $additionalPathsToLoad));
        }

        return new NavigationRouteResponse($categories);
    }

    /**
     * @param list<string> $additionalPaths
     */
    private function loadAdditionalPaths(
        ChannelContext $context,
        Criteria $criteria,
        array $additionalPaths
    ): NavigationCollection {
        $criteria->addFilter(new EqualsAnyFilter('path', $additionalPaths));

        $criteria->addAssociation('media');

        $criteria->setLimit(null);
        $criteria->setTotalCountMode(Criteria::TOTAL_COUNT_MODE_NONE);

        return $this->navigationRepository->search($criteria, $context)->getEntities();
    }

    /**
     * @return array<string, NavigationMetaInformation>
     */
    private function getNavigationMetaInfo(string $activeId, string $rootId): array
    {
        $result = $this->connection->fetchAllAssociative('
            # navigation-route::meta-information
            SELECT LOWER(HEX(`id`)), `path`, `level`
            FROM `navigation`
            WHERE `id` = :activeId OR `id` = :rootId
        ', ['activeId' => Uuid::fromHexToBytes($activeId), 'rootId' => Uuid::fromHexToBytes($rootId)]);

        if (!$result) {
            throw NavigationException::navigationNotFound($activeId);
        }

        /** @var array<string, NavigationMetaInformation> $result */
        $result = FetchModeHelper::groupUnique($result);

        return $result;
    }

    /**
     * @param array<string, NavigationMetaInformation> $metaInfo
     *
     * @return NavigationMetaInformation
     */
    private function getMetaInfoById(string $id, array $metaInfo): array
    {
        if (!\array_key_exists($id, $metaInfo)) {
            throw NavigationException::navigationNotFound($id);
        }

        return $metaInfo[$id];
    }

    private function validate(string $activeId, ?string $path, ChannelContext $context): void
    {
        $ids = array_filter([
            $context->getChannel()->getFooterNavigationId(),
            $context->getChannel()->getServiceNavigationId(),
            $context->getChannel()->getNavigationId(),
        ]);

        foreach ($ids as $id) {
            if ($this->isChildNavigation($activeId, $path, $id)) {
                return;
            }
        }

        throw NavigationException::navigationNotFound($activeId);
    }

    private function isChildNavigation(string $activeId, ?string $path, string $rootId): bool
    {
        if ($rootId === $activeId) {
            return true;
        }

        if ($path === null) {
            return false;
        }

        if (mb_strpos($path, '|' . $rootId . '|') !== false) {
            return true;
        }

        return false;
    }
}
