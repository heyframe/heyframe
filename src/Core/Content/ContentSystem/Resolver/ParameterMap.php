<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Resolver;

use HeyFrame\Core\Content\ContentSystem\ContentSystemException;
use HeyFrame\Core\Framework\Log\Package;

/**
 * Map of scalar parameters (name => scalar value).
 *
 * Represents pass-through parameters that don't require entity resolution.
 * These are directly injected into placeholder resolution.
 *
 * Example: ['page' => 1, 'sort' => 'name', 'active' => true]
 *
 * @internal
 */
#[Package('discovery')]
class ParameterMap
{
    /**
     * @param array<string, int|string|bool|float> $map Parameter name => Scalar value
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
     * Get parameter by name.
     */
    public function get(string $name): int|string|bool|float|null
    {
        return $this->map[$name] ?? null;
    }

    /**
     * Check if parameter exists.
     */
    public function has(string $name): bool
    {
        return isset($this->map[$name]);
    }

    /**
     * Add parameter (returns new instance).
     */
    public function add(string $name, int|string|bool|float $value): self
    {
        $map = $this->map;
        $map[$name] = $value;

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
     * @return array<string, int|string|bool|float>
     */
    public function toArray(): array
    {
        return $this->map;
    }

    /**
     * Validate that all keys are strings and values are scalar.
     */
    private function validate(): void
    {
        foreach ($this->map as $key => $value) {
            if (!\is_string($key)) {
                throw ContentSystemException::invalidMapKey('Parameter map', get_debug_type($key));
            }

            if (!\is_scalar($value)) {
                throw ContentSystemException::invalidMapValue('Parameter map', $key, 'scalar (int, string, bool, float)', get_debug_type($value));
            }
        }
    }
}
