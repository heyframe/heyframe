<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Promotion\Gateway;

use HeyFrame\Core\Checkout\Promotion\PromotionCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
interface PromotionGatewayInterface
{
    /**
     * Gets a list of promotions for the provided criteria and
     * sales channel context.
     */
    public function get(Criteria $criteria, ChannelContext $context): PromotionCollection;
}
