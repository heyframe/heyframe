<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel;

use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('inventory')]
abstract class AbstractProductCloseoutFilterFactory
{
    abstract public function getDecorated(): AbstractProductCloseoutFilterFactory;

    abstract public function create(ChannelContext $context): MultiFilter;
}
