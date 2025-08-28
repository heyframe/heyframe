<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Detail;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('inventory')]
abstract class AbstractAvailableCombinationLoader
{
    abstract public function getDecorated(): AbstractAvailableCombinationLoader;

    abstract public function loadCombinations(string $productId, ChannelContext $channelContext): AvailableCombinationResult;
}
