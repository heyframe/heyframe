<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Snippet\Struct;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Collection;

/**
 * @extends Collection<InvalidPluralizationStruct>
 */
#[Package('discovery')]
class InvalidPluralizationCollection extends Collection
{
    protected function getExpectedClass(): string
    {
        return InvalidPluralizationStruct::class;
    }
}
