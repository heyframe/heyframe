<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Cookie\Channel;

use HeyFrame\Core\Content\Cookie\Struct\CookieGroupCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\StoreApiResponse;

/**
 * @codeCoverageIgnore
 *
 * @extends StoreApiResponse<CookieGroupCollection>
 */
#[Package('framework')]
class CookieRouteResponse extends StoreApiResponse
{
    public function getCookieGroups(): CookieGroupCollection
    {
        return $this->object;
    }
}
