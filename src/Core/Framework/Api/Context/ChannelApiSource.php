<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Api\Context;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\JsonSerializableTrait;

#[Package('framework')]
class ChannelApiSource implements ContextSource, \JsonSerializable
{
    use JsonSerializableTrait;

    public string $type = 'sales-channel';

    public function __construct(private readonly string $channelId)
    {
    }

    public function getChannelId(): string
    {
        return $this->channelId;
    }
}
