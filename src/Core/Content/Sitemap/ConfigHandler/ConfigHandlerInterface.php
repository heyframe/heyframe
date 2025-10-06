<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Sitemap\ConfigHandler;

use HeyFrame\Core\Framework\Log\Package;

#[Package('discovery')]
interface ConfigHandlerInterface
{
    /**
     * @return array<string, array<array<string, mixed>>>
     */
    public function getSitemapConfig(): array;
}
