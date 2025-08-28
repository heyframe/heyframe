<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Context;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('framework')]
abstract class AbstractChannelContextFactory
{
    abstract public function getDecorated(): AbstractChannelContextFactory;

    /**
     * @param array<string, string|array<string,bool>|null> $options
     */
    abstract public function create(string $token, string $channelId, array $options = []): ChannelContext;
}
