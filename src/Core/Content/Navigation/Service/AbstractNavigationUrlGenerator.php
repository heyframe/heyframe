<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Navigation\Service;

use HeyFrame\Core\Content\Navigation\NavigationEntity;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelEntity;

#[Package('discovery')]
abstract class AbstractNavigationUrlGenerator
{
    abstract public function getDecorated(): AbstractNavigationUrlGenerator;

    abstract public function generate(NavigationEntity $navigation, ?ChannelEntity $channel): ?string;
}
