<?php
declare(strict_types=1);

namespace HeyFrame\Frontend\Theme\ConfigLoader;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
abstract class AbstractAvailableThemeProvider
{
    abstract public function getDecorated(): AbstractAvailableThemeProvider;

    /**
     * @return array<string, string>
     */
    abstract public function load(Context $context, bool $activeOnly): array;
}
