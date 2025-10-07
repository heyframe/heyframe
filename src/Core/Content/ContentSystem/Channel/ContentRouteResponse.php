<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Channel;

use HeyFrame\Core\Content\ContentSystem\Channel\Struct\ContentPageStruct;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\StoreApiResponse;

/**
 * @extends StoreApiResponse<ContentPageStruct>
 */
#[Package('discovery')]
class ContentRouteResponse extends StoreApiResponse
{
    public function getContentPage(): ContentPageStruct
    {
        return $this->object;
    }
}
