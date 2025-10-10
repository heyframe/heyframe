<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Routing\LayoutResolution\Cascade;

use HeyFrame\Core\Content\ContentSystem\Routing\IdResolution\Struct\ResolvedData;
use HeyFrame\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\PartialEntity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * Resolves layout via association traversal.
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

        $criteria = new Criteria([$sourceId]);
        $criteria->addAssociation($this->associationName);

        $associationCriteria = $criteria->getAssociation($this->associationName);
        $associationCriteria->addFields(['id']);

        $definition = $this->definitionRegistry->getByEntityName($sourceType);
        $repository = $this->definitionRegistry->getRepository($definition->getEntityName());

        $entity = $repository->search($criteria, $context->getContext())->first();
        if (!$entity) {
            return [];
        }

        $getter = 'get' . \ucfirst($this->associationName);
        if (!\method_exists($entity, $getter)) {
            return [];
        }

        /** @var EntityCollection<Entity>|null $associated */
        $associated = $entity->$getter(); // @phpstan-ignore-line

        if ($associated === null) {
            return [];
        }

        return $associated->getIds();
    }

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
