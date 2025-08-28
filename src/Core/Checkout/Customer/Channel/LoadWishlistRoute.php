<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerWishlist\CustomerWishlistCollection;
use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerWishlist\CustomerWishlistEntity;
use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Checkout\Customer\CustomerException;
use HeyFrame\Core\Checkout\Customer\Event\CustomerWishlistLoaderCriteriaEvent;
use HeyFrame\Core\Checkout\Customer\Event\CustomerWishlistProductListingResultEvent;
use HeyFrame\Core\Content\Product\Channel\AbstractProductCloseoutFilterFactory;
use HeyFrame\Core\Content\Product\ProductCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\StoreApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Entity\ChannelRepository;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StoreApiRouteScope::ID]])]
#[Package('checkout')]
class LoadWishlistRoute extends AbstractLoadWishlistRoute
{
    /**
     * @internal
     *
     * @param EntityRepository<CustomerWishlistCollection> $wishlistRepository
     * @param ChannelRepository<ProductCollection> $productRepository
     */
    public function __construct(
        private readonly EntityRepository $wishlistRepository,
        private readonly ChannelRepository $productRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly SystemConfigService $systemConfigService,
        private readonly AbstractProductCloseoutFilterFactory $productCloseoutFilterFactory
    ) {
    }

    public function getDecorated(): AbstractLoadWishlistRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/store-api/customer/wishlist', name: 'store-api.customer.wishlist.load', methods: ['GET', 'POST'], defaults: ['_loginRequired' => true, '_entity' => 'product'])]
    public function load(Request $request, ChannelContext $context, Criteria $criteria, CustomerEntity $customer): LoadWishlistRouteResponse
    {
        if (!$criteria->getTitle()) {
            $criteria->setTitle('wishlist::load-products');
        }

        if (!$this->systemConfigService->get('core.cart.wishlistEnabled', $context->getChannelId())) {
            throw CustomerException::customerWishlistNotActivated();
        }

        $wishlist = $this->loadWishlist($context, $customer->getId());
        $products = $this->loadProducts($wishlist->getId(), $criteria, $context, $request);

        return new LoadWishlistRouteResponse($wishlist, $products);
    }

    private function loadWishlist(ChannelContext $context, string $customerId): CustomerWishlistEntity
    {
        $criteria = (new Criteria())
            ->setLimit(1)
            ->addFilter(new MultiFilter(MultiFilter::CONNECTION_AND, [
                new EqualsFilter('customerId', $customerId),
                new EqualsFilter('channelId', $context->getChannelId()),
            ]));

        $result = $this->wishlistRepository->search($criteria, $context->getContext())->getEntities()->first();
        if (!$result) {
            throw CustomerException::customerWishlistNotFound();
        }

        return $result;
    }

    /**
     * @return EntitySearchResult<ProductCollection>
     */
    private function loadProducts(string $wishlistId, Criteria $criteria, ChannelContext $context, Request $request): EntitySearchResult
    {
        $criteria
            ->addFilter(new EqualsFilter('wishlists.wishlistId', $wishlistId))
            ->addSorting(new FieldSorting('wishlists.updatedAt', FieldSorting::DESCENDING))
            ->addSorting(new FieldSorting('wishlists.createdAt', FieldSorting::DESCENDING));

        if ($this->systemConfigService->getBool(
            'core.listing.hideCloseoutProductsWhenOutOfStock',
            $context->getChannelId()
        )) {
            $criteria->addFilter($this->productCloseoutFilterFactory->create($context));
        }

        $event = new CustomerWishlistLoaderCriteriaEvent($criteria, $context);
        $this->eventDispatcher->dispatch($event);

        $products = $this->productRepository->search($criteria, $context);

        $event = new CustomerWishlistProductListingResultEvent($request, $products, $context);
        $this->eventDispatcher->dispatch($event);

        return $products;
    }
}
