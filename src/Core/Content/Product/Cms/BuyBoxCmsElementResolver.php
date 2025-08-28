<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Cms;

use HeyFrame\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use HeyFrame\Core\Content\Cms\Channel\Struct\BuyBoxStruct;
use HeyFrame\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use HeyFrame\Core\Content\Cms\DataResolver\ResolverContext\EntityResolverContext;
use HeyFrame\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use HeyFrame\Core\Content\Product\Aggregate\ProductReview\ProductReviewCollection;
use HeyFrame\Core\Content\Product\Channel\ChannelProductEntity;
use HeyFrame\Core\Content\Product\Channel\Detail\ProductConfiguratorLoader;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Aggregation\Metric\CountAggregation;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\AggregationResult\Metric\CountResult;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('discovery')]
class BuyBoxCmsElementResolver extends AbstractProductDetailCmsElementResolver
{
    /**
     * @internal
     *
     * @param EntityRepository<ProductReviewCollection> $repository
     */
    public function __construct(
        private readonly ProductConfiguratorLoader $configuratorLoader,
        private readonly EntityRepository $repository
    ) {
    }

    public function getType(): string
    {
        return 'buy-box';
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $buyBox = new BuyBoxStruct();
        $slot->setData($buyBox);

        $productConfig = $slot->getFieldConfig()->get('product');
        if ($productConfig === null) {
            return;
        }

        $product = null;

        if ($productConfig->isMapped() && $resolverContext instanceof EntityResolverContext) {
            $product = $this->resolveEntityValue($resolverContext->getEntity(), $productConfig->getStringValue());
        }

        if ($productConfig->isStatic()) {
            $product = $this->getSlotProduct($slot, $result, $productConfig->getStringValue());
        }

        /** @var ChannelProductEntity|null $product */
        if ($product !== null) {
            $buyBox->setProduct($product);
            $buyBox->setProductId($product->getId());
            $buyBox->setConfiguratorSettings($this->configuratorLoader->load($product, $resolverContext->getChannelContext()));
            $buyBox->setTotalReviews($this->getReviewsCount($product, $resolverContext->getChannelContext()));
        }
    }

    private function getReviewsCount(ChannelProductEntity $product, ChannelContext $context): int
    {
        $reviewCriteria = $this->createReviewCriteria($context, $product->getParentId() ?? $product->getId());

        $aggregation = $this->repository->aggregate($reviewCriteria, $context->getContext())->get('review-count');

        return $aggregation instanceof CountResult ? $aggregation->getCount() : 0;
    }

    private function createReviewCriteria(ChannelContext $context, string $productId): Criteria
    {
        $reviewFilters = [];
        $criteria = new Criteria();

        $reviewFilters[] = new EqualsFilter('status', true);
        if ($context->getCustomer()) {
            $reviewFilters[] = new EqualsFilter('customerId', $context->getCustomerId());
        }

        $criteria->addFilter(
            new MultiFilter(MultiFilter::CONNECTION_AND, [
                new MultiFilter(MultiFilter::CONNECTION_OR, $reviewFilters),
                new MultiFilter(MultiFilter::CONNECTION_OR, [
                    new EqualsFilter('product.id', $productId),
                    new EqualsFilter('product.parentId', $productId),
                ]),
            ])
        );

        $criteria->addAggregation(new CountAggregation('review-count', 'id'));

        return $criteria;
    }
}
