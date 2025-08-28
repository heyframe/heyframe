<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Promotion\Cart\Discount\Filter;

use HeyFrame\Core\Checkout\Promotion\Cart\Discount\DiscountPackageCollection;
use HeyFrame\Core\Framework\Log\Package;

#[Package('checkout')]
interface FilterSorterInterface
{
    public function getKey(): string;

    public function sort(DiscountPackageCollection $packages): DiscountPackageCollection;
}
