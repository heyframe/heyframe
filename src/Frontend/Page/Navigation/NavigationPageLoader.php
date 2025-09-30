<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Page\Navigation;

use HeyFrame\Core\Content\Navigation\Channel\AbstractLoadNavigationRoute;
use HeyFrame\Core\Content\Navigation\NavigationEntity;
use HeyFrame\Core\Content\Navigation\NavigationException;
use HeyFrame\Core\Content\Seo\SeoUrlPlaceholderHandlerInterface;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\ChannelEntity;
use HeyFrame\Frontend\Page\GenericPageLoaderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

#[Package('framework')]
class NavigationPageLoader implements NavigationPageLoaderInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly GenericPageLoaderInterface $genericLoader,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly AbstractLoadNavigationRoute $cmsPageRoute,
        private readonly SeoUrlPlaceholderHandlerInterface $seoUrlReplacer
    ) {
    }

    public function load(Request $request, ChannelContext $context): NavigationPage
    {
        $page = $this->genericLoader->load($request, $context);
        $page = NavigationPage::createFrom($page);

        $navigationId = $request->get('navigationId', $context->getChannel()->getNavigationId());

        $this->eventDispatcher->dispatch(
            new NavigationPageLoadedEvent($page, $context, $request)
        );
        $navigation = $this->cmsPageRoute
            ->load($navigationId, $request, $context)
            ->getNavigation();

        if (!$navigation->getActive()) {
            throw NavigationException::navigationNotFound($navigation->getId());
        }

        $this->loadMetaData($navigation, $page, $context->getChannel());
        $page->setNavigationId($navigation->getId());
        $page->setNavigation($navigation);

        if ($page->getMetaInformation()) {
            $canonical = ($navigationId === $context->getChannel()->getNavigationId())
                ? $this->seoUrlReplacer->generate('frontend.home.page')
                : $this->seoUrlReplacer->generate('frontend.navigation.page', ['navigationId' => $navigationId]);

            $page->getMetaInformation()->setCanonical($canonical);
        }
        $this->eventDispatcher->dispatch(
            new NavigationPageLoadedEvent($page, $context, $request)
        );

        return $page;
    }

    private function loadMetaData(NavigationEntity $category, NavigationPage $page, ChannelEntity $channel): void
    {
        $metaInformation = $page->getMetaInformation();

        if ($metaInformation === null) {
            return;
        }

        $isHome = $channel->getNavigationId() === $category->getId();

        $metaDescription = $isHome && $channel->getTranslation('homeMetaDescription')
            ? $channel->getTranslation('homeMetaDescription')
            : $category->getTranslation('metaDescription')
            ?? $category->getTranslation('description');
        $metaInformation->setMetaDescription((string) $metaDescription);

        $metaTitle = $isHome && $channel->getTranslation('homeMetaTitle')
            ? $channel->getTranslation('homeMetaTitle')
            : $category->getTranslation('metaTitle')
            ?? $category->getTranslation('name');
        $metaInformation->setMetaTitle((string) $metaTitle);

        $keywords = $isHome && $channel->getTranslation('homeKeywords')
            ? $channel->getTranslation('homeKeywords')
            : $category->getTranslation('keywords');
        $metaInformation->setMetaKeywords((string) $keywords);
    }
}
