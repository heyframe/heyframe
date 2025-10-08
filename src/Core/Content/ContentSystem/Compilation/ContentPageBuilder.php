<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Compilation;

use HeyFrame\Core\Content\ContentSystem\Channel\Struct\ContentPageStruct;
use HeyFrame\Core\Content\ContentSystem\ContentLayout\ContentLayoutCollection;
use HeyFrame\Core\Content\ContentSystem\ContentLayout\ContentLayoutEntity;
use HeyFrame\Core\Content\ContentSystem\Resolver\Struct\ResolvedData;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('discovery')]
class ContentPageBuilder
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

    public function build(string $layoutId, ResolvedData $resolvedData, ChannelContext $context): ?ContentPageStruct
    {
        $criteria = new Criteria([$layoutId]);
        $layout = $this->contentLayoutRepository->search($criteria, $context->getContext())->first();

        if (!$layout instanceof ContentLayoutEntity) {
            return null;
        }

        $contentLayout = $layout->getLayout();
        $refinedLayout = $this->refinery->refine($contentLayout, $resolvedData, $context);

        $contentPage = new ContentPageStruct(
            $layoutId,
            $resolvedData,
            null,
            []
        );

        $contentPage->setLayout($refinedLayout);
        $contentPage->setLayoutName($layout->getName());
        $contentPage->setLayoutVersion($layout->getVersion());

        return $contentPage;
    }
}
