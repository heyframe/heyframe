<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Channel;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\FrontApiResponse;

/**
 * @extends FrontApiResponse<ChannelContext>
 */
#[Package('framework')]
class ContextLoadRouteResponse extends FrontApiResponse
{
    public function getContext(): ChannelContext
    {
        return $this->object;
    }
}
