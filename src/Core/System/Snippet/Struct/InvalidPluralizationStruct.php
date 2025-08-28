<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Snippet\Struct;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Struct;

#[Package('discovery')]
class InvalidPluralizationStruct extends Struct
{
    public function __construct(
        public readonly string $snippetKey,
        public readonly string $snippetValue,
        public readonly bool $isFixable,
        public readonly string $path,
    ) {
    }
}
