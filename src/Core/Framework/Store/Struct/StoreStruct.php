<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Store\Struct;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Struct;

/**
 * @codeCoverageIgnore
 */
#[Package('checkout')]
abstract class StoreStruct extends Struct
{
    /**
     * @param array<string, mixed> $data
     */
    abstract public static function fromArray(array $data): self;
}
