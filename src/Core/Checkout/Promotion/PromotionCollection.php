<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Promotion;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<PromotionEntity>
 */
#[Package('checkout')]
class PromotionCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'promotion_collection';
    }

    protected function getExpectedClass(): string
    {
        return PromotionEntity::class;
    }
}
