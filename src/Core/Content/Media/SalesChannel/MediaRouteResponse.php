<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Media\SalesChannel;

use HeyFrame\Core\Content\Media\MediaCollection;
use HeyFrame\Core\System\SalesChannel\StoreApiResponse;

/**
 * @extends StoreApiResponse<MediaCollection>
 */
class MediaRouteResponse extends StoreApiResponse
{
    public function getMediaCollection(): MediaCollection
    {
        return $this->object;
    }
}
