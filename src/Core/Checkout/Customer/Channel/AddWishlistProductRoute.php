<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerWishlist\CustomerWishlistCollection;
use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Checkout\Customer\CustomerException;
use HeyFrame\Core\Checkout\Customer\Event\WishlistProductAddedEvent;
use HeyFrame\Core\Content\Product\ProductCollection;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\StoreApiRouteScope;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Entity\ChannelRepository;
use HeyFrame\Core\System\Channel\SuccessResponse;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StoreApiRouteScope::ID]])]
#[Package('checkout')]
class AddWishlistProductRoute extends AbstractAddWishlistProductRoute
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
        private readonly SystemConfigService $systemConfigService,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function getDecorated(): AbstractAddWishlistProductRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/store-api/customer/wishlist/add/{productId}', name: 'store-api.customer.wishlist.add', methods: ['POST'], defaults: ['_loginRequired' => true])]
    public function add(string $productId, ChannelContext $context, CustomerEntity $customer): SuccessResponse
    {
        if (!$this->systemConfigService->get('core.cart.wishlistEnabled', $context->getChannelId())) {
            throw CustomerException::customerWishlistNotActivated();
        }

        $this->validateProduct($productId, $context);
        $wishlistId = $this->getWishlistId($context, $customer->getId());

        $this->wishlistRepository->upsert([
            [
                'id' => $wishlistId,
                'customerId' => $customer->getId(),
                'channelId' => $context->getChannelId(),
                'products' => [
                    [
                        'productId' => $productId,
                        'productVersionId' => Defaults::LIVE_VERSION,
                    ],
                ],
            ],
        ], $context->getContext());

        $this->eventDispatcher->dispatch(new WishlistProductAddedEvent($wishlistId, $productId, $context));

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

        return $this->wishlistRepository->searchIds($criteria, $context->getContext())->firstId() ?? Uuid::randomHex();
    }

    private function validateProduct(string $productId, ChannelContext $context): void
    {
        $total = $this->productRepository->searchIds(new Criteria([$productId]), $context)->getTotal();
        if ($total === 0) {
            throw CustomerException::productNotFound($productId);
        }
    }
}
