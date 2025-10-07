<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Compilation;

use HeyFrame\Core\Content\ContentSystem\Channel\Struct\ContentPageStruct;
use HeyFrame\Core\Content\ContentSystem\ContentLayout\ContentLayoutCollection;
use HeyFrame\Core\Content\ContentSystem\ContentLayout\ContentLayoutEntity;
use HeyFrame\Core\Content\ContentSystem\Resolver\Struct\ResolvedData;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;

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
        private readonly PlaceholderFillerService $placeholderFiller
    ) {
    }

    /**
     * Builds a content page from a layout ID and resolved data.
     */
    public function build(string $layoutId, ResolvedData $resolvedData, Context $context): ?ContentPageStruct
    {
        // Load layout from database
        $criteria = new Criteria([$layoutId]);
        $layout = $this->contentLayoutRepository->search($criteria, $context)->first();

        if (!$layout instanceof ContentLayoutEntity) {
            return null;
        }

        // Get layout structure
        $structure = $layout->getStructure();

        // Fill placeholders in structure
        $filledStructure = $this->placeholderFiller->fill($structure, $resolvedData);

        // Create content page struct
        $contentPage = new ContentPageStruct(
            $layoutId,
            $resolvedData,
            null, // route will be set later
            [] // matched parameters will be set later
        );

        // Add filled structure to content page
        $contentPage->setStructure($filledStructure);
        $contentPage->setLayoutName($layout->getName());
        $contentPage->setLayoutVersion($layout->getVersion());

        return $contentPage;
    }
}
