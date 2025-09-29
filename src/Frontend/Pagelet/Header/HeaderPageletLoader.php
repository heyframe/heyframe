<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Pagelet\Header;

use HeyFrame\Core\Content\Navigation\Service\NavigationLoaderInterface;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use HeyFrame\Core\Framework\Feature;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Routing\RoutingException;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\ChannelException;
use HeyFrame\Core\System\Currency\Channel\AbstractCurrencyRoute;
use HeyFrame\Core\System\Currency\CurrencyCollection;
use HeyFrame\Core\System\Language\Channel\AbstractLanguageRoute;
use HeyFrame\Core\System\Language\LanguageCollection;
use HeyFrame\Frontend\Event\RouteRequest\CurrencyRouteRequestEvent;
use HeyFrame\Frontend\Event\RouteRequest\LanguageRouteRequestEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Do not use direct or indirect repository calls in a PageletLoader. Always use a store-api route to get or put data.
 */
#[Package('framework')]
class HeaderPageletLoader implements HeaderPageletLoaderInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly NavigationLoaderInterface $navigationLoader
    ) {
    }

    /**
     * @throws RoutingException
     */
    public function load(Request $request, ChannelContext $context): HeaderPagelet
    {
        $channel = $context->getChannel();

        $navigation = $this->navigationLoader->load(
            $channel->getNavigationId(),
            $context,
            $channel->getNavigationId(),
            $channel->getNavigationDepth()
        );

        $page = new HeaderPagelet(
            $navigation,
        );
        $this->eventDispatcher->dispatch(new HeaderPageletLoadedEvent($page, $context, $request));

        return $page;
    }
}
