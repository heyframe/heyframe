<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Routing\LayoutResolution\Cascade;

use HeyFrame\Core\Content\ContentSystem\Layout\Entity\ContentLayoutAssignmentEntity;
use HeyFrame\Core\Content\ContentSystem\Routing\IdResolution\Struct\ResolvedData;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * Cascade step using polymorphism instead of conditionals.
 *
 * @internal
 */
#[Package('discovery')]
interface CascadeStepInterface
{
    /**
     * @return array<MultiFilter>
     */
    public function buildFilters(ResolvedData $data, ChannelContext $context): array;

    /**
     * @param EntityCollection<ContentLayoutAssignmentEntity> $assignments
     */
    public function resolve(EntityCollection $assignments, ResolvedData $data, ChannelContext $context): ?string;
}
