<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Theme\FrontendPluginConfiguration;

use HeyFrame\Core\Framework\Bundle;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
abstract class AbstractFrontendPluginConfigurationFactory
{
    abstract public function getDecorated(): AbstractFrontendPluginConfigurationFactory;

    abstract public function createFromBundle(Bundle $bundle): FrontendPluginConfiguration;

    /**
     * @param array<string, mixed> $data
     */
    abstract public function createFromThemeJson(string $name, array $data, string $path): FrontendPluginConfiguration;
}
