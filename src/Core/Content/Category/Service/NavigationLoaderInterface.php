<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Category\Service;

use HeyFrame\Core\Content\Category\Tree\Tree;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('discovery')]
interface NavigationLoaderInterface
{
    /**
     * Returns the first two levels of the category tree, as well as all parents of the active category
     * and the active categories first level of children.
     * The provided active id will be marked as selected
     */
    public function load(string $activeId, ChannelContext $context, string $rootId, int $depth = 2): Tree;
}
