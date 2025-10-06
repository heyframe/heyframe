<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Sitemap\Service;

use HeyFrame\Core\Framework\Log\Package;

#[Package('discovery')]
interface SitemapHandleInterface
{
    public function write(array $urls): void;

    public function finish(): void;
}
