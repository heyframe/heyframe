<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Element\Slot;

use HeyFrame\Core\Content\ContentSystem\Element\Runtime\ContentElement;
use HeyFrame\Core\Framework\Log\Package;

/**
 * Value object representing the content of a single slot.
 *
 * A slot contains zero or more ContentElement instances.
 * Provides convenient access methods for slot content.
 *
 * Immutable - modifications return new instances.
 *
 * @implements \IteratorAggregate<int, ContentElement>
 *
 * @internal
 */
#[Package('discovery')]
class SlotContent implements \IteratorAggregate, \Countable
{
    /**
     * @param array<ContentElement> $elements
     */
    public function __construct(
        private readonly array $elements = []
    ) {
    }

    /**
     * Create empty slot content.
     */
    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * Get first element in slot.
     */
    public function first(): ?ContentElement
    {
        return $this->elements[0] ?? null;
    }

    /**
     * Get last element in slot.
     */
    public function last(): ?ContentElement
    {
        if (empty($this->elements)) {
            return null;
        }

        return $this->elements[array_key_last($this->elements)];
    }

    /**
     * Get element by index.
     */
    public function get(int $index): ?ContentElement
    {
        // Reindex to ensure sequential keys
        $reindexed = array_values($this->elements);

        return $reindexed[$index] ?? null;
    }

    /**
     * Check if slot is empty.
     */
    public function isEmpty(): bool
    {
        return empty($this->elements);
    }

    /**
     * Check if slot has content.
     */
    public function hasContent(): bool
    {
        return !empty($this->elements);
    }

    /**
     * Get all elements as array.
     *
     * @return array<ContentElement>
     */
    public function all(): array
    {
        return $this->elements;
    }

    /**
     * Add element to slot (returns new instance).
     */
    public function add(ContentElement $element): self
    {
        $elements = $this->elements;
        $elements[] = $element;

        return new self($elements);
    }

    /**
     * Filter elements by predicate (returns new instance).
     *
     * @param callable(ContentElement): bool $predicate
     */
    public function filter(callable $predicate): self
    {
        $filtered = array_filter($this->elements, $predicate);

        return new self(array_values($filtered));
    }

    /**
     * Map elements (returns array of mapped values).
     *
     * @template T
     *
     * @param callable(ContentElement): T $mapper
     *
     * @return array<T>
     */
    public function map(callable $mapper): array
    {
        return array_map($mapper, $this->elements);
    }

    /**
     * Convert to array structure (serialization).
     *
     * @return array<int, array<string, mixed>>
     */
    public function toArray(): array
    {
        return array_map(
            fn (ContentElement $element) => $element->toArray(),
            $this->elements
        );
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->elements);
    }

    public function count(): int
    {
        return \count($this->elements);
    }
}
