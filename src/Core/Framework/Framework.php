<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework;

/**
 * @internal
 */
class Framework extends Bundle
{
    public function getTemplatePriority(): int
    {
        return -1;
    }
}
