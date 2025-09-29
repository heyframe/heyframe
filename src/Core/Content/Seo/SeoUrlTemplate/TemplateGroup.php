<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Seo\SeoUrlTemplate;

use HeyFrame\Core\Framework\Log\Package;

#[Package('inventory')]
class TemplateGroup
{
    /**
     * @param array<string> $channelIds
     */
    public function __construct(
        private readonly string $languageId,
        private readonly string $template,
        private readonly array $channelIds,
        private array $channels = []
    ) {
    }

    public function getLanguageId(): string
    {
        return $this->languageId;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function getChannelIds(): array
    {
        return $this->channelIds;
    }

    public function getChannels(): array
    {
        return $this->channels;
    }

    public function setChannels(array $channels): void
    {
        $this->channels = $channels;
    }
}
