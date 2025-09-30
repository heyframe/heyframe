<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Navigation\Service;

use HeyFrame\Core\Content\Navigation\Tree\Tree;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('discovery')]
interface NavigationLoaderInterface
{
    public function load(string $activeId, ChannelContext $context, string $rootId, int $depth = 2): Tree;
}
