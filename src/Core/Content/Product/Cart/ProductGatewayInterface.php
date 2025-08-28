<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Cart;

use HeyFrame\Core\Content\Product\ProductCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('inventory')]
interface ProductGatewayInterface
{
    /**
     * @param array<string> $ids
     */
    public function get(array $ids, ChannelContext $context): ProductCollection;
}
