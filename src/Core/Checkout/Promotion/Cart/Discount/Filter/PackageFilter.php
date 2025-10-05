<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Promotion\Cart\Discount\Filter;

use HeyFrame\Core\Checkout\Promotion\Cart\Discount\DiscountLineItem;
use HeyFrame\Core\Checkout\Promotion\Cart\Discount\DiscountPackageCollection;
use HeyFrame\Core\Framework\Log\Package;

#[Package('checkout')]
abstract class PackageFilter
{
    abstract public function getDecorated(): PackageFilter;

    abstract public function filterPackages(DiscountLineItem $discount, DiscountPackageCollection $packages, int $originalPackageCount): DiscountPackageCollection;
}
