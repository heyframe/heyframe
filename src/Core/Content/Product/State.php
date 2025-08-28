<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product;

use HeyFrame\Core\Framework\Log\Package;

#[Package('inventory')]
final class State
{
    public const IS_PHYSICAL = 'is-physical';
    public const IS_DOWNLOAD = 'is-download';
}
