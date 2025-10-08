<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\DataContext;

use HeyFrame\Core\Framework\Log\Package;

/**
 * Stack-based context management for hierarchical data distribution.
 *
 * Manages data contexts during content element tree traversal using a LIFO
 * (Last In, First Out) stack pattern. This enables proper scoping where
 * inner providers can override outer providers for the same context key.
 *
 * Example:
 * - Outer element provides "product" → pushed to stack
 * - Inner element provides "product" → pushed to stack (shadows outer)
 * - Child elements see inner "product"
 * - Inner scope ends → popped from stack
 * - Child elements now see outer "product" again
 *
 * Each context entry stores:
 * - data: The actual context data (entity, collection, etc.)
 * - distribution: Distribution strategy identifier (broadcast, iterator, etc.)
 *
 * @internal
 */
#[Package('discovery')]
class DataContextStack
{
    /**
     * Stack storage: key => array of context entries.
     *
     * @var array<string, array<int, array{data: mixed, distribution: string}>>
     */
    private array $stack = [];

    /**
     * Push a new context onto the stack.
     *
     * @param string $key Context key (e.g., 'product', 'category')
     * @param mixed $data Context data to distribute
     * @param string $distribution Distribution strategy (e.g., 'broadcast', 'iterator')
     */
    public function push(string $key, mixed $data, string $distribution): void
    {
        if (!isset($this->stack[$key])) {
            $this->stack[$key] = [];
        }

        $this->stack[$key][] = [
            'data' => $data,
            'distribution' => $distribution,
        ];
    }

    /**
     * Pop the topmost context from the stack.
     *
     * @param string $key Context key to pop
     */
    public function pop(string $key): void
    {
        if (!isset($this->stack[$key])) {
            return;
        }

        array_pop($this->stack[$key]);

        // Clean up empty stacks
        if (empty($this->stack[$key])) {
            unset($this->stack[$key]);
        }
    }

    /**
     * Get the current context for a key.
     *
     * Returns the topmost context entry with both data and distribution metadata.
     *
     * @param string $key Context key to retrieve
     *
     * @return array{data: mixed, distribution: string}|null
     */
    public function get(string $key): ?array
    {
        if (!isset($this->stack[$key]) || empty($this->stack[$key])) {
            return null;
        }

        // Return topmost entry (most recent push)
        return end($this->stack[$key]);
    }

    /**
     * Check if a context key exists in the stack.
     *
     * @param string $key Context key to check
     */
    public function has(string $key): bool
    {
        return isset($this->stack[$key]) && !empty($this->stack[$key]);
    }

    /**
     * Get current stack depth for a key (for debugging/testing).
     *
     * @param string $key Context key
     *
     * @return int Stack depth (0 if not present)
     *
     * @internal
     */
    public function getDepth(string $key): int
    {
        return isset($this->stack[$key]) ? \count($this->stack[$key]) : 0;
    }

    /**
     * Clear all contexts from the stack (for testing).
     *
     * @internal
     */
    public function clear(): void
    {
        $this->stack = [];
    }
}
