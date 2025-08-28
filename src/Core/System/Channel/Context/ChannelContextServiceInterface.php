<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Context;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * @internal
 */
#[Package('framework')]
interface ChannelContextServiceInterface
{
    public function get(ChannelContextServiceParameters $parameters): ChannelContext;
}
