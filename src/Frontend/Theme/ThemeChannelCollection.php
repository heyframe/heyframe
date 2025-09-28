<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Theme;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Collection;

/**
 * @extends Collection<ThemeChannel>
 */
#[Package('framework')]
class ThemeChannelCollection extends Collection
{
    protected function getExpectedClass(): string
    {
        return ThemeChannel::class;
    }
}
