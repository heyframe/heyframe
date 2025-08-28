<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Media\Event;

use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('discovery')]
class UnusedMediaSearchStartEvent
{
    public function __construct(public int $totalMedia, public int $totalMediaDeletionCandidates)
    {
    }
}
