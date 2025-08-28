<?php declare(strict_types=1);

namespace HeyFrame\Core\System\SystemConfig;

use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
abstract class AbstractSystemConfigLoader
{
    abstract public function getDecorated(): AbstractSystemConfigLoader;

    abstract public function load(?string $channelId): array;
}
