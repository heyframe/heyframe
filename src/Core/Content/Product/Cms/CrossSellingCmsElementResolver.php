<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Cms;

use HeyFrame\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use HeyFrame\Core\Content\Cms\Channel\Struct\CrossSellingStruct;
use HeyFrame\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use HeyFrame\Core\Content\Cms\DataResolver\ResolverContext\EntityResolverContext;
use HeyFrame\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use HeyFrame\Core\Content\Product\Channel\ChannelProductEntity;
use HeyFrame\Core\Content\Product\Channel\CrossSelling\AbstractProductCrossSellingRoute;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Request;

#[Package('discovery')]
class CrossSellingCmsElementResolver extends AbstractProductDetailCmsElementResolver
{
    final public const TYPE = 'cross-selling';

    /**
     * @internal
     */
    public function __construct(private readonly AbstractProductCrossSellingRoute $crossSellingLoader)
    {
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $config = $slot->getFieldConfig();
        $context = $resolverContext->getChannelContext();
        $struct = new CrossSellingStruct();
        $slot->setData($struct);

        $productConfig = $config->get('product');

        if ($productConfig === null || $productConfig->getValue() === null) {
            return;
        }

        $product = null;

        if ($productConfig->isMapped() && $resolverContext instanceof EntityResolverContext) {
            $product = $this->resolveEntityValue($resolverContext->getEntity(), $productConfig->getStringValue());
        }

        if ($productConfig->isStatic()) {
            $product = $this->getSlotProduct($slot, $result, $productConfig->getStringValue());
        }

        if (!$product instanceof ChannelProductEntity) {
            return;
        }

        $crossSellings = $this->crossSellingLoader->load($product->getId(), new Request(), $context, new Criteria())->getResult();

        if ($crossSellings->count()) {
            $struct->setCrossSellings($crossSellings);
        }
    }
}
