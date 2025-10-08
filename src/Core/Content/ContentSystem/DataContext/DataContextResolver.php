<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\DataContext;

use HeyFrame\Core\Content\ContentSystem\Element\Runtime\ContentElement;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * Resolves data contexts between provider and consumer elements.
 *
 * Orchestrates data flow in the content element tree by:
 * 1. Identifying provider elements (those with provides_context)
 * 2. Pushing their data to a hierarchical context stack
 * 3. Identifying consumer elements (those with accepts_context)
 * 4. Distributing data to consumers using registered distribution strategies
 * 5. Cleaning up context when leaving provider scope
 *
 * Data Flow Example:
 * ```
 * product-detail (provides "product" via broadcast)
 *   ├─ product-header (accepts "product") → receives product
 *   ├─ product-gallery (accepts "product") → receives product
 *   └─ product-info (accepts "product") → receives product
 * ```
 *
 * Supports nested scopes where inner providers can shadow outer providers:
 * ```
 * category-page (provides "category")
 *   ├─ category-header (accepts "category") → receives outer category
 *   └─ featured-products (provides "product")
 *       └─ product-box (accepts "product") → receives inner product, not category
 * ```
 *
 * Supports five distribution strategies:
 * - broadcast: Single entity to all children
 * - indexed: Collection items by position
 * - iterator: Template repeated for each item (with element cloning)
 * - keyed: Collection items by specific keys
 * - sliced: Collection divided into chunks
 *
 * @internal
 */
#[Package('discovery')]
class DataContextResolver
{
    /**
     * @param iterable<DistributionStrategyInterface> $strategies Distribution strategies
     */
    public function __construct(
        private readonly iterable $strategies
    ) {
    }

    /**
     * Resolve data contexts for the entire element tree.
     *
     * @param ContentElement $element Root element to process
     * @param ChannelContext $context Sales channel context
     */
    public function resolve(ContentElement $element, ChannelContext $context): void
    {
        $stack = new DataContextStack();
        $this->resolveRecursive($element, $stack, $context);
    }

    /**
     * Recursively resolve contexts for element and its children.
     *
     * @param ContentElement $element Current element
     * @param DataContextStack $stack Context stack for hierarchical data
     * @param ChannelContext $context Sales channel context
     */
    private function resolveRecursive(ContentElement $element, DataContextStack $stack, ChannelContext $context): void
    {
        $providesContext = $element->getProvidesContext();

        if ($providesContext !== []) {
            foreach ($providesContext as $contextKey => $providerDef) {
                $data = $element->getProperty($contextKey);
                $distributionConfig = $providerDef->getDistribution();
                $distribution = $distributionConfig->getStrategy()->value;

                if ($data !== null) {
                    $stack->push($contextKey, $data, $distribution);

                    $this->distributeContextToChildren(
                        $element,
                        $contextKey,
                        $data,
                        $distribution,
                        $distributionConfig->toArray()
                    );
                }
            }
        }

        // Accept context from ancestors (not siblings - siblings handled by parent's distribution above)
        $acceptsContext = $element->getAcceptsContext();
        if ($acceptsContext !== []) {
            foreach ($acceptsContext as $contextKey => $consumerDef) {
                if (!$element->hasProperty($contextKey) && $stack->has($contextKey)) {
                    $contextData = $stack->get($contextKey);
                    if ($contextData !== null) {
                        $element->setProperty($contextKey, $contextData['data']);
                    }
                }
            }
        }

        $this->processChildren($element, $stack, $context);

        if ($providesContext !== []) {
            foreach ($providesContext as $contextKey => $providerDef) {
                $stack->pop($contextKey);
            }
        }
    }

    /**
     * Process child elements using ElementSlots API.
     *
     * @param ContentElement $element Parent element
     * @param DataContextStack $stack Context stack
     * @param ChannelContext $context Sales channel context
     */
    private function processChildren(ContentElement $element, DataContextStack $stack, ChannelContext $context): void
    {
        foreach ($element->getSlots()->allElements() as $child) {
            $this->resolveRecursive($child, $stack, $context);
        }
    }

    /**
     * Find matching distribution strategy.
     *
     * @param string $distribution Distribution type identifier
     *
     * @return DistributionStrategyInterface|null Matching strategy or null
     */
    private function findStrategy(string $distribution): ?DistributionStrategyInterface
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->supports($distribution)) {
                return $strategy;
            }
        }

        return null;
    }

    /**
     * Distribute context data to child consumers using appropriate strategy.
     *
     * @param ContentElement $providerElement Provider element
     * @param string $contextKey Context key being provided
     * @param mixed $data Data to distribute
     * @param string $distribution Distribution type
     * @param array<string, mixed> $config Distribution configuration
     */
    private function distributeContextToChildren(
        ContentElement $providerElement,
        string $contextKey,
        mixed $data,
        string $distribution,
        array $config
    ): void {
        $consumers = $providerElement->collectConsumers($contextKey);

        if ($consumers === []) {
            return;
        }

        $strategy = $this->findStrategy($distribution);

        if ($strategy === null) {
            // Fallback to broadcast if no strategy found
            foreach ($consumers as $consumer) {
                $consumer->setProperty($contextKey, $data);
            }

            return;
        }

        // Strategy expects array format for compatibility with unit tests
        $consumerData = array_map(fn (ContentElement $el) => [
            'type' => $el->getType(),
            'data_key' => $el->getProperties()['data_key'] ?? null,
        ], $consumers);

        $distributed = $strategy->distribute($data, $consumerData, $config);

        foreach ($consumers as $index => $consumer) {
            if (isset($distributed[$index])) {
                $consumer->setProperty($contextKey, $distributed[$index]);
            }
        }
    }
}
