<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Media;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<MediaEntity>
 */
class MediaCollection extends EntityCollection
{
    /**
     * @return array<array-key, string>
     */
    public function getUserIds(): array
    {
        return $this->fmap(fn (MediaEntity $media) => $media->getUserId());
    }

    public function filterByUserId(string $id): self
    {
        return $this->filter(fn (MediaEntity $media) => $media->getUserId() === $id);
    }

    public function getApiAlias(): string
    {
        return 'media_collection';
    }

    protected function getExpectedClass(): string
    {
        return MediaEntity::class;
    }
}
