<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\MessageQueue\ScheduledTask;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<ScheduledTaskEntity>
 */
#[Package('framework')]
class ScheduledTaskCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'dal_scheduled_task_collection';
    }

    protected function getExpectedClass(): string
    {
        return ScheduledTaskEntity::class;
    }
}
