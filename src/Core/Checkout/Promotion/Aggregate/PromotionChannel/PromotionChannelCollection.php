<?php
declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionChannel;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<PromotionChannelEntity>
 */
#[Package('checkout')]
class PromotionChannelCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'promotion_channel_collection';
    }

    protected function getExpectedClass(): string
    {
        return PromotionChannelEntity::class;
    }
}
