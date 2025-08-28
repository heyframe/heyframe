<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Context;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\BaseChannelContext;

/**
 * Loads customer-independent information for a sales channel, which could be cached separately.
 *
 * @internal
 */
#[Package('framework')]
abstract class AbstractBaseChannelContextFactory
{
    /**
     * @param array<string, mixed> $options
     */
    abstract public function create(string $channelId, array $options = []): BaseChannelContext;
}
