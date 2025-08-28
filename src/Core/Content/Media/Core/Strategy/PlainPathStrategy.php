<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Media\Core\Strategy;

use HeyFrame\Core\Content\Media\Core\Application\AbstractMediaPathStrategy;

/**
 * @internal Concrete implementation is not allowed to be decorated or extended. The implementation details can change
 */
class PlainPathStrategy extends AbstractMediaPathStrategy
{
    public function name(): string
    {
        return 'plain';
    }
}
