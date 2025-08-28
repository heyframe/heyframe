<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product;

use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('inventory')]
abstract class AbstractProductMaxPurchaseCalculator
{
    abstract public function getDecorated(): AbstractProductMaxPurchaseCalculator;

    abstract public function calculate(Entity $product, ChannelContext $context): int;
}
