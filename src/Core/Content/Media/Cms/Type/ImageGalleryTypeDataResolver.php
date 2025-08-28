<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Media\Cms\Type;

use HeyFrame\Core\Framework\Log\Package;

#[Package('discovery')]
class ImageGalleryTypeDataResolver extends ImageSliderTypeDataResolver
{
    public function getType(): string
    {
        return 'image-gallery';
    }
}
