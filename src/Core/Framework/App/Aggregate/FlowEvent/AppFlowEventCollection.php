<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Aggregate\FlowEvent;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<AppFlowEventEntity>
 */
#[Package('framework')]
class AppFlowEventCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'app_flow_event_collection';
    }

    protected function getExpectedClass(): string
    {
        return AppFlowEventEntity::class;
    }
}
