<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Pagelet\Footer;

use HeyFrame\Core\Checkout\Payment\Channel\AbstractPaymentMethodRoute;
use HeyFrame\Core\Checkout\Payment\PaymentMethodCollection;
use HeyFrame\Core\Content\Navigation\NavigationCollection;
use HeyFrame\Core\Content\Navigation\Service\NavigationLoaderInterface;
use HeyFrame\Core\Content\Navigation\Tree\TreeItem;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Frontend\Event\RouteRequest\PaymentMethodRouteRequestEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Do not use direct or indirect repository calls in a PageletLoader. Always use a store-api route to get or put data.
 */
#[Package('framework')]
class FooterPageletLoader implements FooterPageletLoaderInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly NavigationLoaderInterface $navigationLoader,
        private readonly AbstractPaymentMethodRoute $paymentMethodRoute,
    ) {
    }

    public function load(Request $request, ChannelContext $channelContext): FooterPagelet
    {
        $footerId = $channelContext->getChannel()->getFooterNavigationId();

        $tree = null;
        if ($footerId) {
            $tree = $this->navigationLoader->load($footerId, $channelContext, $footerId);
        }
        $pagelet = new FooterPagelet(
            $tree,
            $this->loadServiceMenu($channelContext),
            $this->loadPaymentMethods($request, $channelContext),
        );

        $this->eventDispatcher->dispatch(
            new FooterPageletLoadedEvent($pagelet, $channelContext, $request)
        );

        return $pagelet;
    }

    private function loadServiceMenu(ChannelContext $context): NavigationCollection
    {
        $serviceId = $context->getChannel()->getServiceNavigationId();

        if ($serviceId === null) {
            return new NavigationCollection();
        }

        $navigation = $this->navigationLoader->load($serviceId, $context, $serviceId, 1);

        return new NavigationCollection(array_map(static fn (TreeItem $treeItem) => $treeItem->getNavigation(), $navigation->getTree()));
    }

    private function loadPaymentMethods(Request $request, ChannelContext $channelContext): PaymentMethodCollection
    {
        $criteria = new Criteria();
        $criteria->setTitle('footer-pagelet::payment-methods');

        $event = new PaymentMethodRouteRequestEvent($request, $request->duplicate(), $channelContext, $criteria);
        $this->eventDispatcher->dispatch($event);

        return $this->paymentMethodRoute
            ->load($event->getStoreApiRequest(), $channelContext, $event->getCriteria())
            ->getPaymentMethods();
    }
}
