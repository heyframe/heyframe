<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerWishlist\CustomerWishlistCollection;
use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Checkout\Customer\CustomerException;
use HeyFrame\Core\Checkout\Customer\Event\WishlistMergedEvent;
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
use HeyFrame\Core\Framework\Validation\DataBag\DataBag;
use HeyFrame\Core\Framework\Validation\DataBag\RequestDataBag;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Entity\ChannelRepository;
use HeyFrame\Core\System\Channel\SuccessResponse;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StoreApiRouteScope::ID]])]
#[Package('checkout')]
class MergeWishlistProductRoute extends AbstractMergeWishlistProductRoute
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
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly Connection $connection
    ) {
    }

    public function getDecorated(): AbstractMergeWishlistProductRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/store-api/customer/wishlist/merge', name: 'store-api.customer.wishlist.merge', methods: ['POST'], defaults: ['_loginRequired' => true])]
    public function merge(RequestDataBag $data, ChannelContext $context, CustomerEntity $customer): SuccessResponse
    {
        if (!$this->systemConfigService->get('core.cart.wishlistEnabled', $context->getChannelId())) {
            throw CustomerException::customerWishlistNotActivated();
        }

        $wishlistId = $this->getWishlistId($context, $customer->getId());

        $upsertData = $this->buildUpsertProducts($data, $wishlistId, $context);

        $this->wishlistRepository->upsert([[
            'id' => $wishlistId,
            'customerId' => $customer->getId(),
            'channelId' => $context->getChannelId(),
            'products' => $upsertData,
        ]], $context->getContext());

        $this->eventDispatcher->dispatch(new WishlistMergedEvent($upsertData, $context));

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

    /**
     * @return array<array{id: string, productId?: string, productVersionId?: Defaults::LIVE_VERSION}>
     */
    private function buildUpsertProducts(RequestDataBag $data, string $wishlistId, ChannelContext $context): array
    {
        $productIds = $data->get('productIds');
        if (!$productIds instanceof DataBag) {
            throw CustomerException::productIdsParameterIsMissing();
        }

        $ids = array_unique(array_filter($productIds->all()));

        if (\count($ids) === 0) {
            return [];
        }

        /** @var array<string> $ids */
        $ids = $this->productRepository->searchIds(new Criteria($ids), $context)->getIds();

        $customerProducts = $this->loadCustomerProducts($wishlistId, $ids);

        $upsertData = [];

        /** @var string $id * */
        foreach ($ids as $id) {
            if (\array_key_exists($id, $customerProducts)) {
                $upsertData[] = [
                    'id' => $customerProducts[$id],
                ];

                continue;
            }

            $upsertData[] = [
                'id' => Uuid::randomHex(),
                'productId' => $id,
                'productVersionId' => Defaults::LIVE_VERSION,
            ];
        }

        return $upsertData;
    }

    /**
     * @param array<string> $productIds
     *
     * @return array<string, string>
     */
    private function loadCustomerProducts(string $wishlistId, array $productIds): array
    {
        $query = $this->connection->createQueryBuilder();
        $query->select(
            'LOWER(HEX(`product_id`)) as `product_id`',
            'LOWER(HEX(`id`)) as id',
        );
        $query->from('`customer_wishlist_product`');
        $query->where('`customer_wishlist_id` = :id');
        $query->andWhere('`product_id` IN (:productIds)');
        $query->setParameter('id', Uuid::fromHexToBytes($wishlistId));
        $query->setParameter('productIds', Uuid::fromHexToBytesList($productIds), ArrayParameterType::BINARY);
        $result = $query->executeQuery();

        /** @var array<string, string> $values */
        $values = $result->fetchAllKeyValue();

        return $values;
    }
}
