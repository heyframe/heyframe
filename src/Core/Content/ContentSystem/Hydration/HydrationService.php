<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Hydration;

use HeyFrame\Core\Content\ContentSystem\Channel\Struct\ContentPageStruct;
use HeyFrame\Core\Content\ContentSystem\DataContext\DataContextResolver;
use HeyFrame\Core\Content\ContentSystem\Element\Runtime\ContentElement;
use HeyFrame\Core\Content\ContentSystem\TypeRegistry\ContentElementTypeRegistry;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * Hydrates content pages using category-based hydrators (static/service),
 * then resolves data contexts for provider/consumer distribution.
 *
 * @internal
 */
#[Package('discovery')]
class HydrationService
{
    /**
     * @param iterable<ContentElementHydratorInterface> $hydrators All registered hydrators
     * @param ContentElementTypeRegistry $typeRegistry Registry for backward compatibility
     * @param DataContextResolver $contextResolver Data context resolver for distribution
     */
    public function __construct(
        private readonly iterable $hydrators,
        private readonly ContentElementTypeRegistry $typeRegistry,
        private readonly DataContextResolver $contextResolver
    ) {
    }

    /**
     * Hydrate content page with data through multiple phases.
     *
     * @param ContentPageStruct $contentPage Content page to hydrate
     * @param ChannelContext $context Sales channel context for data loading
     */
    public function hydrate(ContentPageStruct $contentPage, ChannelContext $context): void
    {
        $layout = $contentPage->getLayout();

        if ($layout === null) {
            return;
        }

        // Phase 1: Hydrate all elements using appropriate hydrators based on category
        $this->hydrateElement($layout, $context);

        // Phase 2: Resolve data contexts (distribute data between provider and consumer elements)
        $this->contextResolver->resolve($layout, $context);
    }

    /**
     * Hydrate a single element and recurse into children.
     *
     * Uses iterator pattern to find matching hydrator.
     * Falls back to TypeRegistry for backward compatibility with unregistered types.
     */
    private function hydrateElement(ContentElement $element, ChannelContext $context): void
    {
        // Try to find hydrator via iterator pattern (self-describing elements)
        $hydrated = false;
        foreach ($this->hydrators as $hydrator) {
            if ($hydrator->supports($element)) {
                $hydrator->hydrate($element, $context);
                $hydrated = true;
                break;
            }
        }

        // Fallback to TypeRegistry for backward compatibility
        if (!$hydrated && $this->typeRegistry->hasType($element->getType())) {
            $hydrator = $this->typeRegistry->getHydrator($element->getType());
            if ($hydrator->supports($element)) {
                $hydrator->hydrate($element, $context);
            }
        }

        // Recurse into all child elements using ElementSlots API
        foreach ($element->getSlots()->allElements() as $child) {
            $this->hydrateElement($child, $context);
        }
    }
}
