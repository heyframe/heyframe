<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ProductStream\Service;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\Filter;
use HeyFrame\Core\Framework\Log\Package;

#[Package('inventory')]
interface ProductStreamBuilderInterface
{
    /**
     * @return array<int, Filter>
     */
    public function buildFilters(
        string $id,
        Context $context
    ): array;
}
