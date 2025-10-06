<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Sitemap\Service;

use League\Flysystem\FilesystemOperator;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('discovery')]
interface SitemapHandleFactoryInterface
{
    public function create(
        FilesystemOperator $filesystem,
        ChannelContext $context,
        ?string $domain = null,
        ?string $domainId = null,
    ): SitemapHandleInterface;
}
