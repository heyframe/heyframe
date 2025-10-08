<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Resolver\Cascade;

use HeyFrame\Core\Content\ContentSystem\Resolver\Struct\ResolvedData;
use HeyFrame\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\PartialEntity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * Association step - resolves layout via association traversal.
 *
 * Example: ['entity' => 'category', 'via' => 'categories', 'from' => 'product']
 *
 * Process:
 * 1. Load source entity (product) by ID from resolved data
 * 2. Load association (categories) with field selection (IDs only)
 * 3. Build filters for each associated entity ID
 * 4. Match first assignment with layout
 *
 * Use case: Product detail page can use category layouts as fallback.
 *
 * @internal
 */
#[Package('discovery')]
final readonly class AssociationStep implements CascadeStepInterface
{
    public function __construct(
        private string $entityType,
        private string $associationName,
        private ?string $sourceEntityType,
        private DefinitionInstanceRegistry $definitionRegistry
    ) {
    }

    public function buildFilters(ResolvedData $data, ChannelContext $context): array
    {
        $entityIds = $this->getAssociatedEntityIds($data, $context);

        if (empty($entityIds)) {
            return [];
        }

        $filters = [];
        foreach ($entityIds as $entityId) {
            $filters[] = new MultiFilter(MultiFilter::CONNECTION_AND, [
                new EqualsFilter('entityType', $this->entityType),
                new EqualsFilter('entityId', $entityId),
            ]);
        }

        return $filters;
    }

    public function resolve(EntityCollection $assignments, ResolvedData $data, ChannelContext $context): ?string
    {
        $entityIds = $this->getAssociatedEntityIds($data, $context);

        if (empty($entityIds)) {
            return null;
        }

        // Check assignments for associated entities in order
        foreach ($entityIds as $entityId) {
            /** @var PartialEntity|null $assignment */
            $assignment = $assignments->filter(
                fn (PartialEntity $a) => $a->get('entityType') === $this->entityType
                    && $a->get('entityId') === $entityId
            )->first();

            if ($assignment && $assignment->get('layoutId')) {
                return $assignment->get('layoutId');
            }
        }

        return null;
    }

    /**
     * Load associated entity IDs.
     *
     * Example: product → categories → [cat1_id, cat2_id, cat3_id]
     *
     * @return array<string>
     */
    private function getAssociatedEntityIds(ResolvedData $data, ChannelContext $context): array
    {
        $sourceType = $this->sourceEntityType ?? $this->inferSourceEntityType($data);
        if ($sourceType === null) {
            return [];
        }

        $sourceId = $data->getEntityId($sourceType . '_id')
            ?? $data->getEntityId($sourceType);

        if ($sourceId === null) {
            return [];
        }

        // Load source entity with association using field selection
        $criteria = new Criteria([$sourceId]);
        $criteria->addAssociation($this->associationName);

        // Limit association fields to IDs only
        $associationCriteria = $criteria->getAssociation($this->associationName);
        $associationCriteria->addFields(['id']);

        $definition = $this->definitionRegistry->getByEntityName($sourceType);
        $repository = $this->definitionRegistry->getRepository($definition->getEntityName());

        $entity = $repository->search($criteria, $context->getContext())->first();
        if (!$entity) {
            return [];
        }

        // Get associated entities via getter
        $getter = 'get' . \ucfirst($this->associationName);
        if (!\method_exists($entity, $getter)) {
            return [];
        }

        /** @var EntityCollection<Entity>|null $associated */
        // @phpstan-ignore-next-line Variable method call needed for association access
        $associated = $entity->$getter();

        if ($associated === null) {
            return [];
        }

        return $associated->getIds();
    }

    /**
     * Infer source entity type from resolved data when not explicitly provided.
     * Looks for placeholders ending with '_id'.
     */
    private function inferSourceEntityType(ResolvedData $data): ?string
    {
        foreach ($data->getEntityIds()->toArray() as $placeholder => $id) {
            if (\str_ends_with($placeholder, '_id')) {
                return \str_replace('_id', '', $placeholder);
            }
        }

        return null;
    }
}
