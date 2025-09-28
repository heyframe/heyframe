<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Theme\ConfigLoader;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Frontend\Theme\FrontendPluginConfiguration\FrontendPluginConfiguration;

#[Package('framework')]
abstract class AbstractConfigLoader
{
    abstract public function getDecorated(): AbstractConfigLoader;

    abstract public function load(string $themeId, Context $context): FrontendPluginConfiguration;
}
