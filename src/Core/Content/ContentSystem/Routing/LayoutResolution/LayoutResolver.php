<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Routing\LayoutResolution;

use HeyFrame\Core\Content\ContentSystem\Layout\Entity\ContentLayoutAssignmentCollection;
use HeyFrame\Core\Content\ContentSystem\Layout\Entity\ContentLayoutAssignmentEntity;
use HeyFrame\Core\Content\ContentSystem\Routing\IdResolution\Struct\ResolvedData;
use HeyFrame\Core\Content\ContentSystem\Routing\LayoutResolution\Cascade\CascadeStepFactory;
use HeyFrame\Core\Content\ContentSystem\Routing\LayoutResolution\Cascade\LayoutCascade;
use HeyFrame\Core\Content\ContentSystem\Routing\Struct\RouteMatchResult;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * Resolves layouts via cascade (first match wins, array order = priority).
 *
 * @internal
 */
#[Package('discovery')]
final class LayoutResolver
{
    /**
     * @param EntityRepository<ContentLayoutAssignmentCollection> $assignmentRepository
     */
    public function __construct(
        private readonly EntityRepository $assignmentRepository,
        private readonly CascadeStepFactory $cascadeStepFactory
    ) {
    }

    public function resolve(RouteMatchResult $match, ResolvedData $resolvedData, ChannelContext $context): ?string
    {
        $route = $match->getRoute();
        $cascade = LayoutCascade::fromArray($route->getLayoutCascade(), $this->cascadeStepFactory);

        if ($cascade === null) {
            return null;
        }

        $assignments = $this->loadAllAssignments($cascade, $resolvedData, $context);

        return $cascade->resolve($assignments, $resolvedData, $context);
    }

    /**
     * @return EntityCollection<ContentLayoutAssignmentEntity>
     */
    private function loadAllAssignments(
        LayoutCascade $cascade,
        ResolvedData $resolvedData,
        ChannelContext $context
    ): EntityCollection {
        $criteria = new Criteria();

        $criteria->addFields([
            'id',
            'entityType',
            'entityId',
            'layoutId',
            'channelId',
        ]);

        $filters = $cascade->buildFilters($resolvedData, $context);

        if (empty($filters)) {
            return new EntityCollection();
        }

        $criteria->addFilter(new OrFilter($filters));
        $criteria->addFilter(
            new EqualsFilter('channelId', $context->getChannel()->getId())
        );

        $result = $this->assignmentRepository->search($criteria, $context->getContext())->getEntities();

        return $result;
    }
}
