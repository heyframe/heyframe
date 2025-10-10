<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Layout\Refinery;

use HeyFrame\Core\Content\ContentSystem\ContentSystemException;
use HeyFrame\Core\Content\ContentSystem\Layout\Entity\ContentLayoutCollection;
use HeyFrame\Core\Content\ContentSystem\Layout\Entity\ContentLayoutEntity;
use HeyFrame\Core\Content\ContentSystem\Routing\IdResolution\Struct\ResolvedData;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * @final
 */
#[Package('discovery')]
class RefinedLayoutBuilder
{
    /**
     * @internal
     *
     * @param EntityRepository<ContentLayoutCollection> $contentLayoutRepository
     */
    public function __construct(
        private readonly EntityRepository $contentLayoutRepository,
        private readonly LayoutRefinery $refinery
    ) {
    }

    public function build(string $layoutEntityId, ResolvedData $resolvedData, ChannelContext $context): RefinedLayout
    {
        $criteria = new Criteria([$layoutEntityId]);
        $layoutEntity = $this->contentLayoutRepository->search($criteria, $context->getContext())->first();

        if (!$layoutEntity instanceof ContentLayoutEntity) {
            throw ContentSystemException::layoutNotFound($layoutEntityId);
        }

        $contentLayout = $layoutEntity->getLayout();
        $refinedLayout = $this->refinery->refine($contentLayout, $resolvedData, $context);

        return new RefinedLayout($layoutEntity, $refinedLayout);
    }
}
