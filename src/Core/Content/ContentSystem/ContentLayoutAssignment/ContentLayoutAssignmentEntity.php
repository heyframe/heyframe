<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\ContentLayoutAssignment;

use HeyFrame\Core\Content\ContentSystem\ContentLayout\ContentLayoutEntity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelEntity;

#[Package('discovery')]
class ContentLayoutAssignmentEntity extends Entity
{
    use EntityIdTrait;

    protected ?string $entityType = null;

    protected ?string $entityId = null;

    protected string $channelId;

    protected string $layoutId;

    protected ?ContentLayoutEntity $layout = null;

    protected ?ChannelEntity $channel = null;

    public function getEntityType(): ?string
    {
        return $this->entityType;
    }

    public function setEntityType(?string $entityType): void
    {
        $this->entityType = $entityType;
    }

    public function getEntityId(): ?string
    {
        return $this->entityId;
    }

    public function setEntityId(?string $entityId): void
    {
        $this->entityId = $entityId;
    }

    public function getChannelId(): string
    {
        return $this->channelId;
    }

    public function setChannelId(string $channelId): void
    {
        $this->channelId = $channelId;
    }

    public function getLayoutId(): string
    {
        return $this->layoutId;
    }

    public function setLayoutId(string $layoutId): void
    {
        $this->layoutId = $layoutId;
    }

    public function getLayout(): ?ContentLayoutEntity
    {
        return $this->layout;
    }

    public function setLayout(?ContentLayoutEntity $layout): void
    {
        $this->layout = $layout;
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
