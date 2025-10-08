<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Element\DataRequirement;

use HeyFrame\Core\Framework\Log\Package;

/**
 * Collection of data requirements for a content element.
 *
 * Provides convenient access patterns for filtering and iterating requirements.
 * Immutable collection - modifications return new instances.
 *
 * @implements \IteratorAggregate<string, DataRequirement>
 *
 * @internal
 */
#[Package('discovery')]
class DataRequirements implements \IteratorAggregate, \Countable
{
    /**
     * @param array<string, DataRequirement> $requirements Indexed by key
     */
    public function __construct(
        private readonly array $requirements = []
    ) {
    }

    /**
     * Create empty collection.
     */
    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * Add a requirement (returns new instance).
     */
    public function add(DataRequirement $requirement): self
    {
        $requirements = $this->requirements;
        $requirements[$requirement->key] = $requirement;

        return new self($requirements);
    }

    /**
     * Get requirement by key.
     */
    public function get(string $key): ?DataRequirement
    {
        return $this->requirements[$key] ?? null;
    }

    /**
     * Check if requirement exists.
     */
    public function has(string $key): bool
    {
        return isset($this->requirements[$key]);
    }

    /**
     * Filter requirements by loader name (returns new instance).
     */
    public function forLoader(string $loaderName): self
    {
        $filtered = array_filter(
            $this->requirements,
            fn (DataRequirement $req) => $req->matchesLoader($loaderName)
        );

        return new self($filtered);
    }

    /**
     * Filter requirements that need loading (returns new instance).
     */
    public function requiresLoading(): self
    {
        $filtered = array_filter(
            $this->requirements,
            fn (DataRequirement $req) => $req->requiresLoading()
        );

        return new self($filtered);
    }

    /**
     * Filter entity-based requirements (returns new instance).
     */
    public function entityBased(): self
    {
        $filtered = array_filter(
            $this->requirements,
            fn (DataRequirement $req) => $req->isEntityBased()
        );

        return new self($filtered);
    }

    /**
     * Check if collection is empty.
     */
    public function isEmpty(): bool
    {
        return empty($this->requirements);
    }

    /**
     * Get all requirements as array.
     *
     * @return array<string, DataRequirement>
     */
    public function all(): array
    {
        return $this->requirements;
    }

    /**
     * Create from array structure (deserialization).
     *
     * @param array<string, array<string, mixed>> $data
     */
    public static function fromArray(array $data): self
    {
        $requirements = [];

        foreach ($data as $key => $requirementData) {
            // Ensure key is in the data
            $requirementData['key'] = $requirementData['key'] ?? $key;
            $requirements[$key] = DataRequirement::fromArray($requirementData);
        }

        return new self($requirements);
    }

    /**
     * Convert to array structure (serialization).
     *
     * @return array<string, array<string, mixed>>
     */
    public function toArray(): array
    {
        $data = [];

        foreach ($this->requirements as $key => $requirement) {
            $data[$key] = $requirement->toArray();
        }

        return $data;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->requirements);
    }

    public function count(): int
    {
        return \count($this->requirements);
    }
}
