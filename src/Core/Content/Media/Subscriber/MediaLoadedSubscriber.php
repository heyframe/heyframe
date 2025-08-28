<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Media\Subscriber;

use HeyFrame\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailCollection;
use HeyFrame\Core\Content\Media\MediaEntity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;

class MediaLoadedSubscriber
{
    /**
     * @param EntityLoadedEvent<MediaEntity> $event
     */
    public function unserialize(EntityLoadedEvent $event): void
    {
        foreach ($event->getEntities() as $media) {
            if ($media->getMediaTypeRaw()) {
                $media->setMediaType(unserialize($media->getMediaTypeRaw()));
            }

            if ($media->getThumbnails() !== null) {
                continue;
            }

            $thumbnails = match (true) {
                $media->getThumbnailsRo() !== null => unserialize($media->getThumbnailsRo()),
                default => new MediaThumbnailCollection(),
            };

            $media->setThumbnails($thumbnails);
        }
    }
}
