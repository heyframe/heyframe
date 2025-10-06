<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Sitemap\Exception;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\HeyFrameHttpException;

#[Package('discovery')]
class InvalidSitemapKey extends HeyFrameHttpException
{
    public function __construct(string $sitemapKey)
    {
        parent::__construct('Invalid sitemap config key: "{{ sitemapKey }}"', ['sitemapKey' => $sitemapKey]);
    }

    public function getErrorCode(): string
    {
        return 'CONTENT__SITEMAP_INVALID_KEY';
    }
}
