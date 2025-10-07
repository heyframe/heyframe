<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\ContentLayoutAssignment;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<ContentLayoutAssignmentEntity>
 */
#[Package('discovery')]
class ContentLayoutAssignmentCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'content_layout_assignment_collection';
    }

    protected function getExpectedClass(): string
    {
        return ContentLayoutAssignmentEntity::class;
    }
}
