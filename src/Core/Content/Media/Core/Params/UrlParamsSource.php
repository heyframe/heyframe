<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Media\Core\Params;

use HeyFrame\Core\Framework\Log\Package;

#[Package('discovery')]
enum UrlParamsSource
{
    case MEDIA;
    case THUMBNAIL;
}
