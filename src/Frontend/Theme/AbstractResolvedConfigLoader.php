<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Theme;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('framework')]
abstract class AbstractResolvedConfigLoader
{
    abstract public function getDecorated(): AbstractResolvedConfigLoader;

    /**
     * @return array<string, mixed>
     */
    abstract public function load(string $themeId, ChannelContext $context): array;
}
