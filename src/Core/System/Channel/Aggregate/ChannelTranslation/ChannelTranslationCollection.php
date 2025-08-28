<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Aggregate\ChannelTranslation;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<ChannelTranslationEntity>
 */
#[Package('discovery')]
class ChannelTranslationCollection extends EntityCollection
{
    /**
     * @return array<string>
     */
    public function getChannelIds(): array
    {
        return $this->fmap(fn (ChannelTranslationEntity $channelTranslation) => $channelTranslation->getChannelId());
    }

    public function filterByChannelId(string $id): self
    {
        return $this->filter(fn (ChannelTranslationEntity $channelTranslation) => $channelTranslation->getChannelId() === $id);
    }

    /**
     * @return array<string>
     */
    public function getLanguageIds(): array
    {
        return $this->fmap(fn (ChannelTranslationEntity $channelTranslation) => $channelTranslation->getLanguageId());
    }

    public function filterByLanguageId(string $id): self
    {
        return $this->filter(fn (ChannelTranslationEntity $channelTranslation) => $channelTranslation->getLanguageId() === $id);
    }

    public function getApiAlias(): string
    {
        return 'channel_translation_collection';
    }

    protected function getExpectedClass(): string
    {
        return ChannelTranslationEntity::class;
    }
}
