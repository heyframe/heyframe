<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Price\Struct;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Rule\Rule;

#[Package('checkout')]
interface FilterableInterface
{
    public function getFilter(): ?Rule;
}
