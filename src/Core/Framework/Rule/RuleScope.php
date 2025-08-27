<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Rule;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\System\SalesChannel\SalesChannelContext;

abstract class RuleScope
{
    abstract public function getContext(): Context;

    abstract public function getSalesChannelContext(): SalesChannelContext;

    public function getCurrentTime(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }
}
