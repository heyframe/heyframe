<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionDiscountPrice;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<PromotionDiscountPriceEntity>
 */
#[Package('checkout')]
class PromotionDiscountPriceCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'promotion_discount_price_collection';
    }

    protected function getExpectedClass(): string
    {
        return PromotionDiscountPriceEntity::class;
    }
}
