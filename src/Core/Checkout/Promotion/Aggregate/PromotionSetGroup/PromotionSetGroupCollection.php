<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionSetGroup;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<PromotionSetGroupEntity>
 */
#[Package('checkout')]
class PromotionSetGroupCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'promotion_set_group_collection';
    }

    protected function getExpectedClass(): string
    {
        return PromotionSetGroupEntity::class;
    }
}
