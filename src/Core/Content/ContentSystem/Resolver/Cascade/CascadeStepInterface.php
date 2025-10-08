<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Resolver\Cascade;

use HeyFrame\Core\Content\ContentSystem\ContentLayoutAssignment\ContentLayoutAssignmentEntity;
use HeyFrame\Core\Content\ContentSystem\Resolver\Struct\ResolvedData;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * Strategy interface for layout cascade resolution steps.
 *
 * Each implementation represents a distinct resolution strategy:
 * - DirectEntityStep: Direct entity ID lookup
 * - AssociationStep: Association traversal (e.g., product → categories)
 * - DefaultLayoutStep: Fallback to default layout
 *
 * Replaces conditional logic in LayoutResolver with polymorphism.
 *
 * @internal
 */
#[Package('discovery')]
interface CascadeStepInterface
{
    /**
     * Build DAL filter for loading assignments.
     *
     * Returns null if this step cannot be resolved (e.g., missing entity ID).
     * Returns MultiFilter with AND conditions for assignment lookup.
     *
     * @return array<MultiFilter> Array of filters (multiple for association steps)
     */
    public function buildFilters(ResolvedData $data, ChannelContext $context): array;

    /**
     * Resolve layout ID from loaded assignments.
     *
     * Returns first matching layout ID, or null if no match found.
     *
     * @param EntityCollection<ContentLayoutAssignmentEntity> $assignments
     */
    public function resolve(EntityCollection $assignments, ResolvedData $data, ChannelContext $context): ?string;
}
