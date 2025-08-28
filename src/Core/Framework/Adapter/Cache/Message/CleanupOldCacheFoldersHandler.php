<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Adapter\Cache\Message;

use HeyFrame\Core\Framework\Adapter\Cache\CacheClearer;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @internal
 */
#[AsMessageHandler]
#[Package('framework')]
final readonly class CleanupOldCacheFoldersHandler
{
    public function __construct(private CacheClearer $cacheClearer)
    {
    }

    public function __invoke(CleanupOldCacheFolders $message): void
    {
        $this->cacheClearer->cleanupOldContainerCacheDirectories();
    }
}
