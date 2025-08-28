<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Api\Sync;

use Doctrine\DBAL\ConnectionException;
use HeyFrame\Core\Framework\Api\Exception\InvalidSyncOperationException;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
interface SyncServiceInterface
{
    /**
     * @param list<SyncOperation> $operations
     *
     * @throws ConnectionException
     * @throws InvalidSyncOperationException
     */
    public function sync(array $operations, Context $context, SyncBehavior $behavior): SyncResult;
}
