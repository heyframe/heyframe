<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Hydration;

use HeyFrame\Core\Content\ContentSystem\Hydration\DataContext\DataContextResolver;
use HeyFrame\Core\Content\ContentSystem\Hydration\DataLoader\DataLoaderProvider;
use HeyFrame\Core\Content\ContentSystem\Layout\Element\ContentElement;
use HeyFrame\Core\Content\ContentSystem\Layout\Refinery\RefinedLayout;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * Loads data and resolves context for content elements.
 *
 * @internal
 */
#[Package('discovery')]
class ContentElementHydrator
{
    public function __construct(
        private readonly DataLoaderProvider $dataLoaderProvider,
        private readonly DataContextResolver $contextResolver
    ) {
    }

    public function hydrate(RefinedLayout $refinedLayout, ChannelContext $context): void
    {
        $this->hydrateElement($refinedLayout->rootElement, $context);
        $this->contextResolver->resolve($refinedLayout->rootElement);
    }

    private function hydrateElement(ContentElement $element, ChannelContext $context): void
    {
        if ($element->requiresData()) {
            $dataRequirements = $element->getDataRequirements();

            foreach ($dataRequirements as $key => $requirement) {
                $loader = $this->dataLoaderProvider->get($requirement->source);
                $data = $loader->load($element, $requirement, $context);

                if ($data !== null) {
                    $element->setProperty($key, $data);
                }
            }
        }

        foreach ($element->getSlots()->allElements() as $child) {
            $this->hydrateElement($child, $context);
        }
    }
}
