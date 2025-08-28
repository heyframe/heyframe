<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Media\Cms\Type;

class ImageGalleryTypeDataResolver extends ImageSliderTypeDataResolver
{
    public function getType(): string
    {
        return 'image-gallery';
    }
}
