<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Api\Context;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class AdminChannelApiSource extends ChannelApiSource
{
    public string $type = 'admin-sales-channel-api';

    public function __construct(
        string $channelId,
        protected Context $originalContext,
    ) {
        parent::__construct($channelId);
    }

    public function getOriginalContext(): Context
    {
        return $this->originalContext;
    }
}
