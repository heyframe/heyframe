<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Navigation\Channel;

use HeyFrame\Core\Content\Navigation\NavigationCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\FrontApiResponse;

/**
 * @extends FrontApiResponse<NavigationCollection>
 */
#[Package('discovery')]
class NavigationRouteResponse extends FrontApiResponse
{
    public function getNavigations(): NavigationCollection
    {
        return $this->object;
    }
}
