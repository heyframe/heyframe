<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerWishlist\CustomerWishlistCollection;
use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Checkout\Customer\CustomerException;
use HeyFrame\Core\Checkout\Customer\Event\WishlistProductRemovedEvent;
use HeyFrame\Core\Content\Product\ProductCollection;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\StoreApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\SuccessResponse;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StoreApiRouteScope::ID]])]
#[Package('checkout')]
class RemoveWishlistProductRoute extends AbstractRemoveWishlistProductRoute
{
    /**
     * @internal
     *
     * @param EntityRepository<CustomerWishlistCollection> $wishlistRepository
     * @param EntityRepository<ProductCollection> $productRepository
     */
    public function __construct(
        private readonly EntityRepository $wishlistRepository,
        private readonly EntityRepository $productRepository,
        private readonly SystemConfigService $systemConfigService,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function getDecorated(): AbstractRemoveWishlistProductRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/store-api/customer/wishlist/delete/{productId}', name: 'store-api.customer.wishlist.delete', methods: ['DELETE'], defaults: ['_loginRequired' => true])]
    public function delete(string $productId, ChannelContext $context, CustomerEntity $customer): SuccessResponse
    {
        if (!$this->systemConfigService->get('core.cart.wishlistEnabled', $context->getChannelId())) {
            throw CustomerException::customerWishlistNotActivated();
        }

        $wishlistId = $this->getWishlistId($context, $customer->getId());

        $wishlistProductId = $this->getWishlistProductId($wishlistId, $productId, $context);

        $this->productRepository->delete([
            [
                'id' => $wishlistProductId,
            ],
        ], $context->getContext());

        $this->eventDispatcher->dispatch(new WishlistProductRemovedEvent($wishlistId, $productId, $context));

        return new SuccessResponse();
    }

    private function getWishlistId(ChannelContext $context, string $customerId): string
    {
        $criteria = (new Criteria())
            ->setLimit(1)
            ->addFilter(new MultiFilter(MultiFilter::CONNECTION_AND, [
                new EqualsFilter('customerId', $customerId),
                new EqualsFilter('channelId', $context->getChannelId()),
            ]));

        $wishlistId = $this->wishlistRepository->searchIds($criteria, $context->getContext())->firstId();
        if (!$wishlistId) {
            throw CustomerException::customerWishlistNotFound();
        }

        return $wishlistId;
    }

    private function getWishlistProductId(string $wishlistId, string $productId, ChannelContext $context): string
    {
        $criteria = (new Criteria())
            ->setLimit(1)
            ->addFilter(new MultiFilter(MultiFilter::CONNECTION_AND, [
                new EqualsFilter('wishlistId', $wishlistId),
                new EqualsFilter('productId', $productId),
                new EqualsFilter('productVersionId', Defaults::LIVE_VERSION),
            ]));

        $wishlistProductId = $this->productRepository->searchIds($criteria, $context->getContext())->firstId();
        if (!$wishlistProductId) {
            throw CustomerException::wishlistProductNotFound($productId);
        }

        return $wishlistProductId;
    }
}
