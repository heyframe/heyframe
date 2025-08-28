<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ImportExport\Struct;

use HeyFrame\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('fundamentals@after-sales')]
class ImportResult
{
    /**
     * @param EntityWrittenContainerEvent[] $results
     * @param array<int, array<string, mixed>> $failedRecords
     */
    public function __construct(public readonly array $results, public readonly array $failedRecords)
    {
    }
}
