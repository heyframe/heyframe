<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Sitemap\Service;

use HeyFrame\Core\Content\Sitemap\Exception\AlreadyLockedException;
use HeyFrame\Core\Content\Sitemap\Struct\SitemapGenerationResult;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('discovery')]
interface SitemapExporterInterface
{
    public const SITEMAP_URL_LIMIT = 49999;

    public const STRATEGY_MANUAL = 1;
    public const STRATEGY_SCHEDULED_TASK = 2;
    public const STRATEGY_LIVE = 3;

    /**
     * @throws AlreadyLockedException
     */
    public function generate(ChannelContext $context, bool $force = false, ?string $lastProvider = null, ?int $offset = null): SitemapGenerationResult;
}
