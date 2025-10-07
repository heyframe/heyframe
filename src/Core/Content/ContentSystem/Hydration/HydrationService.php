<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Hydration;

use HeyFrame\Core\Content\ContentSystem\Channel\Struct\ContentPageStruct;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;

#[Package('discovery')]
class HydrationService
{
    /**
     * @internal
     */
    public function __construct(
        private readonly DefinitionInstanceRegistry $definitionRegistry
    ) {
    }

    /**
     * Hydrates a content page with full entity data.
     * Loads entities based on resolved IDs from Phase 2.
     */
    public function hydrate(ContentPageStruct $contentPage, Context $context): void
    {
        $resolvedData = $contentPage->getResolvedData();
        $hydratedEntities = [];

        // Load each resolved entity
        foreach ($resolvedData->getEntityIds() as $placeholder => $entityId) {
            // Extract entity type from placeholder (e.g., "product_id" => "product")
            if (\str_ends_with($placeholder, '_id')) {
                $entityType = \str_replace('_id', '', $placeholder);

                try {
                    $definition = $this->definitionRegistry->getByEntityName($entityType);
                    $repository = $this->definitionRegistry->getRepository($definition->getEntityName());

                    $criteria = new Criteria([$entityId]);
                    $entity = $repository->search($criteria, $context)->first();

                    if ($entity instanceof Entity) {
                        $hydratedEntities[$entityType] = $entity;
                    }
                } catch (\Exception) {
                    // Entity type not found or error loading - skip
                    continue;
                }
            }
        }

        $contentPage->setHydratedEntities($hydratedEntities);
    }
}
