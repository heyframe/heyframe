<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\DataAbstractionLayer\StockUpdate;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
abstract class AbstractStockUpdateFilter
{
    /**
     * @param list<string> $ids
     *
     * @return list<string>
     */
    abstract public function filter(array $ids, Context $context): array;
}
