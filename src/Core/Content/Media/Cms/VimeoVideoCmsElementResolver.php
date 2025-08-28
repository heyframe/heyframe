<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Media\Cms;

use HeyFrame\Core\Framework\Log\Package;

#[Package('discovery')]
class VimeoVideoCmsElementResolver extends YoutubeVideoCmsElementResolver
{
    public function getType(): string
    {
        return 'vimeo-video';
    }
}
