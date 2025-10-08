<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Element\Visitor;

use HeyFrame\Core\Content\ContentSystem\Element\Runtime\ContentElement;
use HeyFrame\Core\Framework\Log\Package;

/**
 * Visitor interface for traversing ContentElement trees.
 *
 * Implements the Visitor pattern for processing element hierarchies.
 * Allows different operations (hydration, context resolution, validation, etc.)
 * to be implemented without modifying the ContentElement class.
 *
 * Usage:
 * ```php
 * $element->traverse(new MyVisitor());
 * ```
 *
 * Traversal order:
 * 1. enter() called on current element
 * 2. Traverse children (recursive)
 * 3. leave() called on current element
 *
 * This provides pre-order and post-order processing hooks.
 *
 * @internal
 */
#[Package('discovery')]
interface ElementVisitor
{
    /**
     * Called when entering an element (before processing children).
     *
     * Use this for:
     * - Pre-processing
     * - Pushing state onto stack
     * - Validating element
     */
    public function enter(ContentElement $element): void;

    /**
     * Called when leaving an element (after processing children).
     *
     * Use this for:
     * - Post-processing
     * - Popping state from stack
     * - Collecting results
     */
    public function leave(ContentElement $element): void;
}
