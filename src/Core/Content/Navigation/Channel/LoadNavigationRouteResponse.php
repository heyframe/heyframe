<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Navigation\Channel;

use HeyFrame\Core\Content\Navigation\NavigationEntity;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\FrontApiResponse;


/**
 * @extends FrontApiResponse<NavigationEntity>
 */
#[Package('discovery')]
class LoadNavigationRouteResponse extends FrontApiResponse
{
    public function getNavigation(): NavigationEntity
    {
        return $this->object;
    }
}
