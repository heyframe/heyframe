<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Detail\Event;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\HeyFrameChannelEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('checkout')]
class ResolveVariantIdEvent extends Event implements HeyFrameChannelEvent
{
    public function __construct(
        private readonly string $productId,
        private ?string $resolvedVariantId,
        private readonly ChannelContext $channelContext
    ) {
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function setResolvedVariantId(?string $resolvedVariantId): void
    {
        $this->resolvedVariantId = $resolvedVariantId;
    }

    public function getResolvedVariantId(): ?string
    {
        return $this->resolvedVariantId;
    }

    public function getContext(): Context
    {
        return $this->channelContext->getContext();
    }

    public function getChannelContext(): ChannelContext
    {
        return $this->channelContext;
    }
}
