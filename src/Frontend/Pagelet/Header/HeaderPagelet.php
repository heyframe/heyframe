<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Pagelet\Header;

use HeyFrame\Core\Content\Navigation\Tree\Tree;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Frontend\Pagelet\NavigationPagelet;

#[Package('framework')]
class HeaderPagelet extends NavigationPagelet
{
    /**
     * @internal
     */
    public function __construct(?Tree $navigation)
    {
        parent::__construct($navigation);
    }
}
