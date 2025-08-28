<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Channel;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\StoreApiResponse;

/**
 * @extends StoreApiResponse<ChannelContext>
 */
#[Package('framework')]
class ContextLoadRouteResponse extends StoreApiResponse
{
    public function getContext(): ChannelContext
    {
        return $this->object;
    }
}
