<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Page\Navigation;

use HeyFrame\Core\Content\Navigation\Channel\AbstractLoadNavigationRoute;
use HeyFrame\Core\Content\Navigation\NavigationException;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
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
        $page->setNavigationId($navigation->getId());
        $page->setNavigation($navigation);

        $this->eventDispatcher->dispatch(
            new NavigationPageLoadedEvent($page, $context, $request)
        );

        return $page;
    }
}
