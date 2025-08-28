<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Flow\Aggregate\FlowSequence;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<FlowSequenceEntity>
 */
#[Package('after-sales')]
class FlowSequenceCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'flow_sequence_collection';
    }

    protected function getExpectedClass(): string
    {
        return FlowSequenceEntity::class;
    }
}
