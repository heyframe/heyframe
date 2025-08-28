<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel;

use HeyFrame\Core\Content\Product\ProductCollection;
use HeyFrame\Core\Framework\Log\Package;

#[Package('inventory')]
class ChannelProductCollection extends ProductCollection
{
    protected function getExpectedClass(): string
    {
        return ChannelProductEntity::class;
    }
}
