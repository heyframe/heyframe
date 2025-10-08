<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Hydration;

use HeyFrame\Core\Content\ContentSystem\Element\Runtime\ContentElement;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * Defines the contract for hydrating content elements with data.
 *
 * Hydrators are responsible for loading and populating content elements
 * with the appropriate data based on their type and configuration.
 *
 * Each content element type should have its own hydrator implementation
 * registered in the ContentElementTypeRegistry.
 *
 * @internal
 */
#[Package('discovery')]
interface ContentElementHydratorInterface
{
    /**
     * Check if this hydrator supports the given element type.
     */
    public function supports(ContentElement $element): bool;

    /**
     * Hydrate the element with appropriate data.
     *
     * Hydrators should transform element properties in-place by loading entities,
     * translated content, or service data depending on the element category.
     *
     * Properties are transformed during hydration (e.g., ID string → Entity object).
     *
     * @param ContentElement $element Content element to hydrate
     * @param ChannelContext $context Sales channel context for data loading
     */
    public function hydrate(ContentElement $element, ChannelContext $context): void;
}
