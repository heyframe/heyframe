<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Sitemap\Exception;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\HeyFrameHttpException;

#[Package('discovery')]
class UnknownFileException extends HeyFrameHttpException
{
    public function getErrorCode(): string
    {
        return 'CONTENT__SITEMAP_UNKNOWN_FILE';
    }
}
