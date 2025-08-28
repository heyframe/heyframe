<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Event;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('framework')]
interface HeyFrameChannelEvent extends HeyFrameEvent
{
    public function getChannelContext(): ChannelContext;
}
