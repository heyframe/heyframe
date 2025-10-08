<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Resolver\Parameter;

use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use HeyFrame\Core\Framework\Log\Package;

/**
 * Value object representing entity resolution configuration.
 *
 * Encapsulates how to resolve a route parameter to an entity ID:
 * - Entity type (product, category, etc.)
 * - Match field (id, productNumber, slug, etc.)
 * - Additional constraints (active = true, stock > 0, etc.)
 *
 * Includes behavior for building DAL filters.
 *
 * @internal
 */
#[Package('discovery')]
readonly class EntityResolution
{
    /**
     * @param string $entityType DAL entity name (product, category, etc.)
     * @param string $matchField Field to match parameter value against
     * @param array<string, mixed> $constraints Additional filter constraints
     */
    public function __construct(
        public string $entityType,
        public string $matchField = 'id',
        public array $constraints = []
    ) {
    }

    /**
     * Build filter for matching parameter value.
     */
    public function buildMatchFilter(mixed $value): EqualsFilter
    {
        return new EqualsFilter($this->matchField, $value);
    }

    /**
     * Build filters for additional constraints.
     *
     * @return array<EqualsFilter|RangeFilter|MultiFilter>
     */
    public function buildConstraintFilters(): array
    {
        $filters = [];

        foreach ($this->constraints as $field => $constraint) {
            if (\is_array($constraint)) {
                // Range filter (e.g., {"gte": 100})
                $rangeFilters = [];
                foreach ($constraint as $operator => $value) {
                    $rangeFilters[] = new RangeFilter($field, [
                        $operator => $value,
                    ]);
                }
                $filters[] = new MultiFilter(MultiFilter::CONNECTION_AND, $rangeFilters);
            } else {
                // Simple equals filter
                $filters[] = new EqualsFilter($field, $constraint);
            }
        }

        return $filters;
    }

    /**
     * Check if this resolution has constraints.
     */
    public function hasConstraints(): bool
    {
        return !empty($this->constraints);
    }

    /**
     * Create from array structure.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            entityType: $data['entity'],
            matchField: $data['match_field'] ?? 'id',
            constraints: $data['constraints'] ?? []
        );
    }

    /**
     * Convert to array structure.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'entity' => $this->entityType,
            'match_field' => $this->matchField,
        ];

        if (!empty($this->constraints)) {
            $data['constraints'] = $this->constraints;
        }

        return $data;
    }
}
