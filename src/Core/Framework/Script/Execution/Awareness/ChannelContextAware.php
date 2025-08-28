<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Script\Execution\Awareness;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * Can be implemented by hooks to provide services with the sales channel context.
 * The services can inject the context beforehand and provide a narrow API to the developer.
 *
 * @internal
 */
#[Package('framework')]
interface ChannelContextAware
{
    public function getChannelContext(): ChannelContext;
}
