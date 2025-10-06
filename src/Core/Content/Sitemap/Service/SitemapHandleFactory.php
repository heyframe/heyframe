<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Sitemap\Service;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[Package('discovery')]
class SitemapHandleFactory implements SitemapHandleFactoryInterface
{
    /**
     * @internal
     */
    public function __construct(private readonly EventDispatcherInterface $eventDispatcher)
    {
    }

    public function create(
        FilesystemOperator $filesystem,
        ChannelContext $context,
        ?string $domain = null,
        ?string $domainId = null,
    ): SitemapHandleInterface {
        $domainId = \func_num_args() > 3 ? func_get_arg(3) : null;

        return new SitemapHandle($filesystem, $context, $this->eventDispatcher, $domain, $domainId);
    }
}
