<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\DataAbstractionLayer;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexer;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
abstract class AbstractProductStreamUpdater extends EntityIndexer
{
    /**
     * @param array<string> $ids
     */
    abstract public function updateProducts(array $ids, Context $context): void;
}
