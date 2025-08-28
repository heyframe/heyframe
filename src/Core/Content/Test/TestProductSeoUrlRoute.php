<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Test;

use HeyFrame\Core\Content\Product\ProductDefinition;
use HeyFrame\Core\Content\Product\ProductEntity;
use HeyFrame\Core\Content\Seo\SeoUrlRoute\SeoUrlMapping;
use HeyFrame\Core\Content\Seo\SeoUrlRoute\SeoUrlRouteConfig;
use HeyFrame\Core\Content\Seo\SeoUrlRoute\SeoUrlRouteInterface;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Routing\StoreApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelEntity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @internal
 */
#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StoreApiRouteScope::ID]])]
class TestProductSeoUrlRoute implements SeoUrlRouteInterface
{
    final public const ROUTE_NAME = 'test.product.page';
    final public const DEFAULT_TEMPLATE = '{{ product.id }}';

    public function __construct(private readonly ProductDefinition $productDefinition)
    {
    }

    #[Route(path: '/test/{productId}', name: 'test.product.page', options: ['seo' => true], methods: ['GET'])]
    public function route(): Response
    {
        return new Response();
    }

    public function getConfig(): SeoUrlRouteConfig
    {
        return new SeoUrlRouteConfig(
            $this->productDefinition,
            self::ROUTE_NAME,
            self::DEFAULT_TEMPLATE,
            true
        );
    }

    public function prepareCriteria(Criteria $criteria, ChannelEntity $channel): void
    {
        // no-op, dummy implementation
    }

    /**
     * @param ProductEntity $entity
     */
    public function getMapping(Entity $entity, ?ChannelEntity $channel): SeoUrlMapping
    {
        return new SeoUrlMapping(
            $entity,
            ['productId' => $entity->getId()],
            ['product' => $entity->jsonSerialize()]
        );
    }
}
