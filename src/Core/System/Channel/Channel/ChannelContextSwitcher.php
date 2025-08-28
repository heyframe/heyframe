<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Channel;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Validation\DataBag\DataBag;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('framework')]
class ChannelContextSwitcher
{
    /**
     * @internal
     */
    public function __construct(private readonly AbstractContextSwitchRoute $contextSwitchRoute)
    {
    }

    public function update(DataBag $data, ChannelContext $context): void
    {
        $this->contextSwitchRoute->switchContext($data->toRequestDataBag(), $context);
    }
}
