<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Script\Execution\Awareness;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * @internal
 */
#[Package('framework')]
trait ChannelContextAwareTrait
{
    protected ChannelContext $channelContext;

    public function getChannelContext(): ChannelContext
    {
        return $this->channelContext;
    }
}
