<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Framework\Seo\SeoUrlRoute;

use HeyFrame\Core\Content\Product\ProductDefinition;
use HeyFrame\Core\Content\Product\ProductEntity;
use HeyFrame\Core\Content\Seo\SeoUrlRoute\SeoUrlMapping;
use HeyFrame\Core\Content\Seo\SeoUrlRoute\SeoUrlRouteConfig;
use HeyFrame\Core\Content\Seo\SeoUrlRoute\SeoUrlRouteInterface;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\PartialEntity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelEntity;
use HeyFrame\Frontend\Framework\FrontendFrameworkException;

#[Package('inventory')]
class ProductPageSeoUrlRoute implements SeoUrlRouteInterface
{
    final public const ROUTE_NAME = 'frontend.detail.page';
    final public const DEFAULT_TEMPLATE = '{{ product.translated.name }}/{{ product.productNumber }}';

    /**
     * @internal
     */
    public function __construct(private readonly ProductDefinition $productDefinition)
    {
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
        $criteria->addFilter(new EqualsFilter('active', true));
        $criteria->addFilter(new EqualsFilter('visibilities.channelId', $channel->getId()));
        $criteria->addAssociation('options.group');
    }

    public function getMapping(Entity $product, ?ChannelEntity $channel): SeoUrlMapping
    {
        if (!$product instanceof ProductEntity && !$product instanceof PartialEntity) {
            throw FrontendFrameworkException::invalidArgument('SEO URL Mapping expects argument to be a ProductEntity');
        }

        $categories = $product->get('mainCategories') ?? null;
        if ($categories instanceof EntityCollection && $channel !== null) {
            $filtered = $categories->filter(
                fn (Entity $navigation) => $navigation->get('channelId') === $channel->getId()
            );

            $product->assign(['mainCategories' => $filtered]);
        }

        $productJson = $product->jsonSerialize();

        return new SeoUrlMapping(
            $product,
            ['productId' => $product->getId()],
            [
                'product' => $productJson,
            ]
        );
    }
}
