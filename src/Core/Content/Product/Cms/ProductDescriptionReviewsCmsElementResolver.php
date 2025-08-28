<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Cms;

use HeyFrame\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use HeyFrame\Core\Content\Cms\Channel\Struct\ProductDescriptionReviewsStruct;
use HeyFrame\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use HeyFrame\Core\Content\Cms\DataResolver\ResolverContext\EntityResolverContext;
use HeyFrame\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use HeyFrame\Core\Content\Product\Channel\ChannelProductEntity;
use HeyFrame\Core\Content\Product\Channel\Review\AbstractProductReviewLoader;
use HeyFrame\Core\Content\Product\Channel\Review\ProductReviewsWidgetLoadedHook;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Script\Execution\ScriptExecutor;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;

#[Package('discovery')]
class ProductDescriptionReviewsCmsElementResolver extends AbstractProductDetailCmsElementResolver
{
    final public const TYPE = 'product-description-reviews';

    /**
     * @internal
     */
    public function __construct(
        private readonly AbstractProductReviewLoader $productReviewLoader,
        private readonly ScriptExecutor $scriptExecutor,
        private readonly SystemConfigService $systemConfigService
    ) {
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $data = new ProductDescriptionReviewsStruct();
        $slot->setData($data);

        $productConfig = $slot->getFieldConfig()->get('product');
        if ($productConfig === null) {
            return;
        }

        $request = $resolverContext->getRequest();
        $ratingSuccess = (bool) $request->get('success', false);
        $data->setRatingSuccess($ratingSuccess);

        $product = null;

        if ($productConfig->isMapped() && $resolverContext instanceof EntityResolverContext) {
            $product = $this->resolveEntityValue($resolverContext->getEntity(), $productConfig->getStringValue());
        }

        if ($productConfig->isStatic()) {
            $product = $this->getSlotProduct($slot, $result, $productConfig->getStringValue());
        }

        if (!$product instanceof ChannelProductEntity) {
            // product can not be resolved, so we do not enrich the slot
            return;
        }

        $data->setProduct($product);

        if ($this->systemConfigService->getBool('core.listing.showReview')) {
            $reviews = $this->productReviewLoader->load($request, $resolverContext->getChannelContext(), $product->getId(), $product->getParentId());

            $this->scriptExecutor->execute(new ProductReviewsWidgetLoadedHook($reviews, $resolverContext->getChannelContext()));

            $data->setReviews($reviews);
        }
    }
}
