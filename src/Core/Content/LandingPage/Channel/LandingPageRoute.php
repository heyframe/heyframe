<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\LandingPage\Channel;

use HeyFrame\Core\Content\Cms\Channel\ChannelCmsPageLoaderInterface;
use HeyFrame\Core\Content\Cms\DataResolver\ResolverContext\EntityResolverContext;
use HeyFrame\Core\Content\LandingPage\LandingPageCollection;
use HeyFrame\Core\Content\LandingPage\LandingPageDefinition;
use HeyFrame\Core\Content\LandingPage\LandingPageEntity;
use HeyFrame\Core\Content\LandingPage\LandingPageException;
use HeyFrame\Core\Framework\Adapter\Cache\CacheTagCollector;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\StoreApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Entity\ChannelRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StoreApiRouteScope::ID]])]
#[Package('discovery')]
class LandingPageRoute extends AbstractLandingPageRoute
{
    /**
     * @internal
     *
     * @param ChannelRepository<LandingPageCollection> $landingPageRepository
     */
    public function __construct(
        private readonly ChannelRepository $landingPageRepository,
        private readonly ChannelCmsPageLoaderInterface $cmsPageLoader,
        private readonly LandingPageDefinition $landingPageDefinition,
        private readonly CacheTagCollector $cacheTagCollector,
    ) {
    }

    public static function buildName(string $id): string
    {
        return 'landing-page-route-' . $id;
    }

    public function getDecorated(): AbstractLandingPageRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/store-api/landing-page/{landingPageId}', name: 'store-api.landing-page.detail', methods: ['POST'])]
    public function load(string $landingPageId, Request $request, ChannelContext $context): LandingPageRouteResponse
    {
        $this->cacheTagCollector->addTag(self::buildName($landingPageId));

        $landingPage = $this->loadLandingPage($landingPageId, $context);

        $pageId = $landingPage->getCmsPageId();

        if (!$pageId) {
            return new LandingPageRouteResponse($landingPage);
        }

        $resolverContext = new EntityResolverContext($context, $request, $this->landingPageDefinition, $landingPage);

        $pages = $this->cmsPageLoader->load(
            $request,
            $this->createCriteria($pageId, $request),
            $context,
            $landingPage->getTranslation('slotConfig'),
            $resolverContext
        );

        $cmsPage = $pages->first();
        if ($cmsPage === null) {
            throw LandingPageException::notFound($pageId);
        }

        $landingPage->setCmsPage($cmsPage);

        return new LandingPageRouteResponse($landingPage);
    }

    private function loadLandingPage(string $landingPageId, ChannelContext $context): LandingPageEntity
    {
        $criteria = new Criteria([$landingPageId]);
        $criteria->setTitle('landing-page::data');

        $criteria->addFilter(new EqualsFilter('active', true));
        $criteria->addFilter(new EqualsFilter('channels.id', $context->getChannelId()));

        $landingPage = $this->landingPageRepository->search($criteria, $context)->getEntities()->get($landingPageId);
        if (!$landingPage instanceof LandingPageEntity) {
            throw LandingPageException::notFound($landingPageId);
        }

        return $landingPage;
    }

    private function createCriteria(string $pageId, Request $request): Criteria
    {
        $criteria = new Criteria([$pageId]);
        $criteria->setTitle('landing-page::cms-page');

        $slots = $request->get('slots');

        if (\is_string($slots)) {
            $slots = explode('|', $slots);
        }

        if (!empty($slots) && \is_array($slots)) {
            $criteria
                ->getAssociation('sections.blocks')
                ->addFilter(new EqualsAnyFilter('slots.id', $slots));
        }

        return $criteria;
    }
}
