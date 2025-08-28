<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Rule;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('fundamentals@after-sales')]
abstract class RuleScope
{
    abstract public function getContext(): Context;

    abstract public function getChannelContext(): ChannelContext;

    public function getCurrentTime(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }
}
