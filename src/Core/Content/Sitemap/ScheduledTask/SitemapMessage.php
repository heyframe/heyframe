<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Sitemap\ScheduledTask;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\MessageQueue\AsyncMessageInterface;

#[Package('discovery')]
class SitemapMessage implements AsyncMessageInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly ?string $lastChannelId,
        private readonly ?string $lastLanguageId,
        private readonly ?string $lastProvider,
        private readonly ?int $nextOffset,
        private readonly bool $finished
    ) {
    }

    public function getLastChannelId(): ?string
    {
        return $this->lastChannelId;
    }

    public function getLastLanguageId(): ?string
    {
        return $this->lastLanguageId;
    }

    public function getLastProvider(): ?string
    {
        return $this->lastProvider;
    }

    public function getNextOffset(): ?int
    {
        return $this->nextOffset;
    }

    public function isFinished(): bool
    {
        return $this->finished;
    }
}
