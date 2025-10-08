<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Element\Slot;

use HeyFrame\Core\Content\ContentSystem\Element\Runtime\ContentElement;
use HeyFrame\Core\Framework\Log\Package;

/**
 * Collection of slots for a content element.
 *
 * Manages named slots containing ContentElement instances.
 * Eliminates mixed type handling (array|ContentElement) by normalizing all access.
 *
 * Provides traversal methods for processing all elements across all slots.
 *
 * Immutable - modifications return new instances.
 *
 * @implements \IteratorAggregate<string, SlotContent>
 *
 * @internal
 */
#[Package('discovery')]
class ElementSlots implements \IteratorAggregate, \Countable
{
    /**
     * @param array<string, SlotContent> $slots Indexed by slot name
     */
    public function __construct(
        private readonly array $slots = []
    ) {
    }

    /**
     * Create empty slots collection.
     */
    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * Get content for a specific slot.
     */
    public function get(string $slotName): SlotContent
    {
        return $this->slots[$slotName] ?? SlotContent::empty();
    }

    /**
     * Check if slot exists and has content.
     */
    public function has(string $slotName): bool
    {
        return isset($this->slots[$slotName]) && $this->slots[$slotName]->hasContent();
    }

    /**
     * Check if any slots exist.
     */
    public function isEmpty(): bool
    {
        return empty($this->slots);
    }

    /**
     * Get all slot names.
     *
     * @return array<string>
     */
    public function slotNames(): array
    {
        return array_keys($this->slots);
    }

    /**
     * Add element to slot (returns new instance).
     */
    public function add(string $slotName, ContentElement $element): self
    {
        $slots = $this->slots;
        $currentContent = $slots[$slotName] ?? SlotContent::empty();
        $slots[$slotName] = $currentContent->add($element);

        return new self($slots);
    }

    /**
     * Set entire slot content (returns new instance).
     */
    public function set(string $slotName, SlotContent $content): self
    {
        $slots = $this->slots;
        $slots[$slotName] = $content;

        return new self($slots);
    }

    /**
     * Get all elements across all slots as generator (for traversal).
     *
     * @return \Generator<ContentElement>
     */
    public function allElements(): \Generator
    {
        foreach ($this->slots as $slotContent) {
            foreach ($slotContent as $element) {
                yield $element;
            }
        }
    }

    /**
     * Get total count of elements across all slots.
     */
    public function totalElements(): int
    {
        $count = 0;
        foreach ($this->slots as $slotContent) {
            $count += $slotContent->count();
        }

        return $count;
    }

    /**
     * Filter slots by predicate (returns new instance).
     *
     * @param callable(string, SlotContent): bool $predicate
     */
    public function filter(callable $predicate): self
    {
        $filtered = [];
        foreach ($this->slots as $name => $content) {
            if ($predicate($name, $content)) {
                $filtered[$name] = $content;
            }
        }

        return new self($filtered);
    }

    /**
     * Create from array structure (deserialization).
     *
     * @param array<string, array<int, array<string, mixed>>|array<string, mixed>> $data
     */
    public static function fromArray(array $data): self
    {
        $slots = [];

        foreach ($data as $slotName => $slotData) {
            // Handle both array of elements and single element
            if (isset($slotData['type'])) {
                // Single element (has 'type' key)
                $element = ContentElement::fromArray($slotData);
                $slots[$slotName] = new SlotContent([$element]);
            } else {
                // Array of elements
                $elements = [];
                foreach ($slotData as $elementData) {
                    if (\is_array($elementData)) {
                        $elements[] = ContentElement::fromArray($elementData);
                    }
                }
                $slots[$slotName] = new SlotContent($elements);
            }
        }

        return new self($slots);
    }

    /**
     * Convert to array structure (serialization).
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function toArray(): array
    {
        $data = [];

        foreach ($this->slots as $slotName => $slotContent) {
            $data[$slotName] = $slotContent->toArray();
        }

        return $data;
    }

    /**
     * Iterate over slots (not elements).
     *
     * @return \Traversable<string, SlotContent>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->slots);
    }

    /**
     * Count of slots (not elements).
     */
    public function count(): int
    {
        return \count($this->slots);
    }
}
