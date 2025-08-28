<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel;

use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @final
 */
#[Package('inventory')]
class ProductCloseoutFilter extends NotFilter
{
    public function __construct()
    {
        parent::__construct(self::CONNECTION_AND, [
            new EqualsFilter('product.isCloseout', true),
            new EqualsFilter('product.available', false),
        ]);
    }
}
