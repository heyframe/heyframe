<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\ContentRoute;

use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelEntity;

#[Package('discovery')]
class ContentRouteChannelEntity extends Entity
{
    use EntityIdTrait;

    protected string $contentRouteId;

    protected string $channelId;

    protected ?ContentRouteEntity $contentRoute = null;

    protected ?ChannelEntity $channel = null;

    public function getContentRouteId(): string
    {
        return $this->contentRouteId;
    }

    public function setContentRouteId(string $contentRouteId): void
    {
        $this->contentRouteId = $contentRouteId;
    }

    public function getChannelId(): string
    {
        return $this->channelId;
    }

    public function setChannelId(string $channelId): void
    {
        $this->channelId = $channelId;
    }

    public function getContentRoute(): ?ContentRouteEntity
    {
        return $this->contentRoute;
    }

    public function setContentRoute(?ContentRouteEntity $contentRoute): void
    {
        $this->contentRoute = $contentRoute;
    }

    public function getChannel(): ?ChannelEntity
    {
        return $this->channel;
    }

    public function setChannel(?ChannelEntity $channel): void
    {
        $this->channel = $channel;
    }
}
