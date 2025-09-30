<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Pagelet;

use HeyFrame\Core\Content\Navigation\Tree\Tree;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
abstract class NavigationPagelet extends Pagelet
{
    public function __construct(
        protected ?Tree $navigation,
    ) {
    }

    public function getNavigation(): ?Tree
    {
        return $this->navigation;
    }
}
