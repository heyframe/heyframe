<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Media\Channel;

use HeyFrame\Core\Content\Media\MediaCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\StoreApiResponse;

/**
 * @extends StoreApiResponse<MediaCollection>
 */
#[Package('discovery')]
class MediaRouteResponse extends StoreApiResponse
{
    public function getMediaCollection(): MediaCollection
    {
        return $this->object;
    }
}
