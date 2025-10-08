<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Element\DataRequirement;

use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;

/**
 * Value object representing a single data requirement for a content element.
 *
 * Encapsulates all metadata needed for data loading:
 * - What to load (key, type)
 * - How to load it (loader name)
 * - Optional criteria for filtering
 * - Optional JSON schema for validation
 *
 * This object is immutable and can determine its own loading behavior.
 *
 * @internal
 */
#[Package('discovery')]
readonly class DataRequirement
{
    /**
     * @param string $key Property key where loaded data will be stored
     * @param DataType $type Type of data to load
     * @param string $loader Loader identifier (e.g., 'entity', 'product_listing')
     * @param Criteria|null $criteria Optional DAL criteria for entity loading
     * @param array<string, mixed>|null $schema Optional JSON schema for validation
     */
    public function __construct(
        public string $key,
        public DataType $type,
        public string $loader,
        public ?Criteria $criteria = null,
        public ?array $schema = null
    ) {
    }

    /**
     * Check if this requirement needs data loading.
     */
    public function requiresLoading(): bool
    {
        return $this->type->requiresLoading();
    }

    /**
     * Check if this requirement matches a specific loader.
     */
    public function matchesLoader(string $loaderName): bool
    {
        return $this->loader === $loaderName;
    }

    /**
     * Check if this requirement is entity-based (requires DAL).
     */
    public function isEntityBased(): bool
    {
        return $this->type->isEntityBased();
    }

    /**
     * Create from array structure (deserialization).
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $criteria = null;
        if (isset($data['criteria']) && \is_array($data['criteria'])) {
            $criteria = new Criteria();
            // TODO: Deserialize criteria from array
            // This would require Criteria to support fromArray()
        }

        return new self(
            key: $data['key'],
            type: DataType::from($data['type']),
            loader: $data['loader'],
            criteria: $criteria,
            schema: $data['schema'] ?? null
        );
    }

    /**
     * Convert to array structure (serialization).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'key' => $this->key,
            'type' => $this->type->value,
            'loader' => $this->loader,
        ];

        if ($this->criteria !== null) {
            // TODO: Serialize criteria to array
            // $data['criteria'] = $this->criteria->toArray();
        }

        if ($this->schema !== null) {
            $data['schema'] = $this->schema;
        }

        return $data;
    }
}
