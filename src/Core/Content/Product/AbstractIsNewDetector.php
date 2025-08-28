<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product;

use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('inventory')]
abstract class AbstractIsNewDetector
{
    abstract public function getDecorated(): AbstractIsNewDetector;

    abstract public function isNew(Entity $product, ChannelContext $context): bool;
}
