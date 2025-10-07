<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Resolver;

use HeyFrame\Core\Content\ContentSystem\Resolver\Struct\ResolvedData;
use HeyFrame\Core\Content\ContentSystem\Routing\Struct\RouteMatchResult;
use HeyFrame\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('discovery')]
class EntityIdResolver
{
    /**
     * @internal
     */
    public function __construct(
        protected readonly DefinitionInstanceRegistry $definitionRegistry,
        protected readonly ParameterExtractor $parameterExtractor
    ) {
    }

    public function resolve(RouteMatchResult $match, ChannelContext $context): ?ResolvedData
    {
        $extracted = $this->parameterExtractor->extract($match);
        $resolutionParams = $extracted['resolution'];
        $passthroughParams = $extracted['passthrough'];

        if (empty($resolutionParams)) {
            // No resolution needed, just pass through parameters
            return new ResolvedData([], $passthroughParams);
        }

        // Group by entity type for batch resolution
        $grouped = $this->groupByEntityType($resolutionParams);

        $resolvedEntityIds = [];

        foreach ($grouped as $entityType => $items) {
            $ids = $this->resolveEntityType($entityType, $items, $context);

            if ($ids === null) {
                // Resolution failed for this entity type
                return null;
            }

            $resolvedEntityIds = \array_merge($resolvedEntityIds, $ids);
        }

        // Return separated entity IDs and parameters
        return new ResolvedData($resolvedEntityIds, $passthroughParams);
    }

    /**
     * @param array<string, array{placeholder: string, resolution: array<string, mixed>, value: mixed}> $resolutionParams
     *
     * @return array<string, array<int, array{placeholder: string, resolution: array<string, mixed>, value: mixed}>>
     */
    protected function groupByEntityType(array $resolutionParams): array
    {
        $grouped = [];

        foreach ($resolutionParams as $item) {
            $entityType = $item['resolution']['entity'] ?? null;

            if ($entityType === null) {
                continue;
            }

            $grouped[$entityType][] = $item;
        }

        return $grouped;
    }

    /**
     * Resolves entity IDs for all items of the same type using a single batch query.
     * Uses OR filter to combine all items, while maintaining individual AND constraints per item.
     *
     * @param array<int, array{placeholder: string, resolution: array<string, mixed>, value: mixed}> $items
     *
     * @return array<string, string>|null
     */
    protected function resolveEntityType(string $entityType, array $items, ChannelContext $context): ?array
    {
        $definition = $this->getDefinition($entityType);

        if ($definition === null) {
            return null;
        }

        $repository = $this->definitionRegistry->getRepository($definition->getEntityName());

        // Build batch query with OR filter for all items
        $criteria = new Criteria();

        // Add sales channel visibility filter (applies to entire query)
        $this->addVisibilityFilter($criteria, $entityType, $context);

        // Build OR filter combining all items
        $itemFilters = [];
        $itemsByMatchValue = [];

        foreach ($items as $item) {
            $matchField = $item['resolution']['match_field'] ?? 'id';
            $value = $item['value'];
            $constraints = $item['resolution']['constraints'] ?? [];

            // Each item gets its own AND filter combining match + constraints
            $andFilters = [new EqualsFilter($matchField, $value)];

            // Add constraints for this specific item
            foreach ($constraints as $field => $constraint) {
                $andFilters[] = $this->buildConstraintFilter($field, $constraint);
            }

            // Combine match + constraints with AND
            $itemFilters[] = new MultiFilter(MultiFilter::CONNECTION_AND, $andFilters);

            // Create lookup key for mapping results back
            $lookupKey = $matchField . ':' . $value;
            $itemsByMatchValue[$lookupKey] = $item;
        }

        // Combine all items with OR
        $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, $itemFilters));

        // Add associations to load match fields for mapping
        $matchFields = array_unique(array_map(
            fn ($item) => $item['resolution']['match_field'] ?? 'id',
            $items
        ));

        foreach ($matchFields as $field) {
            if ($field !== 'id') {
                $criteria->addFields(['id', $field]);
            }
        }

        // Execute single batch query
        $result = $repository->search($criteria, $context->getContext());

        // Map results back to placeholders
        $resolvedIds = [];

        foreach ($items as $item) {
            $matchField = $item['resolution']['match_field'] ?? 'id';
            $value = $item['value'];
            $placeholder = $item['placeholder'];

            // Find matching entity from batch results
            $found = false;
            foreach ($result as $entity) {
                $fieldValue = $matchField === 'id' ? $entity->getUniqueIdentifier() : $entity->get($matchField);

                if ($fieldValue === $value) {
                    $resolvedIds[$placeholder] = $entity->getUniqueIdentifier();
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                // Entity not found or constraints not satisfied
                return null;
            }
        }

        return $resolvedIds;
    }

    protected function getDefinition(string $entityType): ?EntityDefinition
    {
        try {
            return $this->definitionRegistry->getByEntityName($entityType);
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Add sales channel visibility filter for entity types that require it.
     */
    protected function addVisibilityFilter(Criteria $criteria, string $entityType, ChannelContext $context): void
    {
        $channelId = $context->getChannel()->getId();

        match ($entityType) {
            'product' => $criteria->addFilter(
                new EqualsFilter('visibilities.channelId', $channelId)
            ),
            'category' => $criteria->addFilter(
                new EqualsFilter('active', true)
            ),
            default => null,
        };
    }

    /**
     * @param mixed $constraint
     */
    protected function buildConstraintFilter(string $field, $constraint): MultiFilter|EqualsFilter|RangeFilter
    {
        if (\is_array($constraint)) {
            // Range filter (e.g., {"gte": 100})
            $filters = [];
            foreach ($constraint as $operator => $value) {
                $filters[] = new RangeFilter($field, [
                    $operator => $value,
                ]);
            }

            return new MultiFilter(MultiFilter::CONNECTION_AND, $filters);
        }

        // Simple equals filter
        return new EqualsFilter($field, $constraint);
    }
}
