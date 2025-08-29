<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel;

use HeyFrame\Core\Content\Product\ProductCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\FrontApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Entity\ChannelRepository;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [FrontApiRouteScope::ID]])]
#[Package('inventory')]
class ProductListRoute extends AbstractProductListRoute
{
    /**
     * @internal
     *
     * @param ChannelRepository<ProductCollection> $productRepository
     */
    public function __construct(private readonly ChannelRepository $productRepository)
    {
    }

    public function getDecorated(): AbstractProductListRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/store-api/product', name: 'store-api.product.search', methods: ['GET', 'POST'], defaults: ['_entity' => 'product'])]
    public function load(Criteria $criteria, ChannelContext $context): ProductListResponse
    {
        return new ProductListResponse($this->productRepository->search($criteria, $context));
    }
}
