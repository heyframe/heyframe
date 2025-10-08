<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Resolver;

use HeyFrame\Core\Content\ContentSystem\ContentSystemException;
use HeyFrame\Core\Framework\Log\Package;

/**
 * Map of entity IDs (placeholder => entity UUID).
 *
 * Represents the result of entity resolution: parameter values have been
 * resolved to actual entity IDs from the database.
 *
 * Example: ['product' => 'uuid-123', 'category' => 'uuid-456']
 *
 * @internal
 */
#[Package('discovery')]
class EntityIdMap
{
    /**
     * @param array<string, string> $map Placeholder name => Entity UUID
     */
    public function __construct(
        private readonly array $map = []
    ) {
        $this->validate();
    }

    /**
     * Create empty map.
     */
    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * Get entity ID by placeholder.
     */
    public function get(string $placeholder): ?string
    {
        return $this->map[$placeholder] ?? null;
    }

    /**
     * Check if placeholder exists.
     */
    public function has(string $placeholder): bool
    {
        return isset($this->map[$placeholder]);
    }

    /**
     * Add entity ID (returns new instance).
     */
    public function add(string $placeholder, string $entityId): self
    {
        $map = $this->map;
        $map[$placeholder] = $entityId;

        return new self($map);
    }

    /**
     * Merge with another map (returns new instance).
     */
    public function merge(self $other): self
    {
        return new self(array_merge($this->map, $other->map));
    }

    /**
     * Check if map is empty.
     */
    public function isEmpty(): bool
    {
        return empty($this->map);
    }

    /**
     * Get all entries as array.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return $this->map;
    }

    /**
     * Validate that all keys and values are strings.
     */
    private function validate(): void
    {
        foreach ($this->map as $key => $value) {
            if (!\is_string($key)) {
                throw ContentSystemException::invalidMapKey('Entity ID map', get_debug_type($key));
            }

            if (!\is_string($value)) {
                throw ContentSystemException::invalidMapValue('Entity ID map', (string) $key, 'string (UUID)', get_debug_type($value));
            }
        }
    }
}
