<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel;

use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('inventory')]
class ProductCloseoutFilterFactory extends AbstractProductCloseoutFilterFactory
{
    public function getDecorated(): AbstractProductCloseoutFilterFactory
    {
        throw new DecorationPatternException(self::class);
    }

    public function create(ChannelContext $context): MultiFilter
    {
        return new ProductCloseoutFilter();
    }
}
