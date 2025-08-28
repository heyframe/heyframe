<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Cms;

use HeyFrame\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use HeyFrame\Core\Content\Cms\Channel\Struct\ProductBoxStruct;
use HeyFrame\Core\Content\Cms\DataResolver\CriteriaCollection;
use HeyFrame\Core\Content\Cms\DataResolver\Element\AbstractCmsElementResolver;
use HeyFrame\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use HeyFrame\Core\Content\Cms\DataResolver\ResolverContext\EntityResolverContext;
use HeyFrame\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use HeyFrame\Core\Content\Product\Channel\ChannelProductEntity;
use HeyFrame\Core\Content\Product\ProductDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;

#[Package('discovery')]
class ProductBoxCmsElementResolver extends AbstractCmsElementResolver
{
    /**
     * @internal
     */
    public function __construct(private readonly SystemConfigService $systemConfigService)
    {
    }

    public function getType(): string
    {
        return 'product-box';
    }

    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection
    {
        $productConfig = $slot->getFieldConfig()->get('product');
        if ($productConfig === null || $productConfig->isMapped() || $productConfig->getValue() === null) {
            return null;
        }

        $criteria = new Criteria([$productConfig->getStringValue()]);
        $criteria->addAssociation('manufacturer');

        $criteriaCollection = new CriteriaCollection();
        $criteriaCollection->add('product_' . $slot->getUniqueIdentifier(), ProductDefinition::class, $criteria);

        return $criteriaCollection;
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $productBox = new ProductBoxStruct();
        $slot->setData($productBox);

        $productConfig = $slot->getFieldConfig()->get('product');
        if ($productConfig === null || $productConfig->getValue() === null) {
            return;
        }

        if ($resolverContext instanceof EntityResolverContext && $productConfig->isMapped()) {
            /** @var ChannelProductEntity $product */
            $product = $this->resolveEntityValue($resolverContext->getEntity(), $productConfig->getStringValue());

            $productBox->setProduct($product);
            $productBox->setProductId($product->getId());
        }

        if ($productConfig->isStatic()) {
            $this->resolveProductFromRemote($slot, $productBox, $result, $productConfig->getStringValue(), $resolverContext->getChannelContext());
        }
    }

    private function resolveProductFromRemote(
        CmsSlotEntity $slot,
        ProductBoxStruct $productBox,
        ElementDataCollection $result,
        string $productId,
        ChannelContext $channelContext
    ): void {
        $product = $result->get('product_' . $slot->getUniqueIdentifier())?->get($productId);
        if (!$product instanceof ChannelProductEntity) {
            return;
        }

        if ($product->getIsCloseout()
            && $product->getStock() <= 0
            && $this->systemConfigService->getBool('core.listing.hideCloseoutProductsWhenOutOfStock', $channelContext->getChannelId())
        ) {
            return;
        }

        $productBox->setProduct($product);
        $productBox->setProductId($product->getId());
    }
}
