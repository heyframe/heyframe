<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Adapter\Cache\Message;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\MessageQueue\AsyncMessageInterface;
use HeyFrame\Core\Framework\MessageQueue\DeduplicatableMessageInterface;

#[Package('framework')]
class CleanupOldCacheFolders implements AsyncMessageInterface, DeduplicatableMessageInterface
{
    /**
     * @experimental stableVersion:v6.8.0 feature:DEDUPLICATABLE_MESSAGES
     */
    public function deduplicationId(): ?string
    {
        return 'cleanup-old-cache-folders';
    }
}
