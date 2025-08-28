<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Media\Core\Strategy;

use HeyFrame\Core\Content\Media\Core\Application\AbstractMediaPathStrategy;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal Concrete implementation is not allowed to be decorated or extended. The implementation details can change
 */
#[Package('discovery')]
class PlainPathStrategy extends AbstractMediaPathStrategy
{
    public function name(): string
    {
        return 'plain';
    }
}
