<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionDiscount;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<PromotionDiscountEntity>
 */
#[Package('checkout')]
class PromotionDiscountCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'promotion_discount_collection';
    }

    protected function getExpectedClass(): string
    {
        return PromotionDiscountEntity::class;
    }
}
