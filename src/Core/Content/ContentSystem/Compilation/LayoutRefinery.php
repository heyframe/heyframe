<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Compilation;

use HeyFrame\Core\Content\ContentSystem\Element\Runtime\ContentElement;
use HeyFrame\Core\Content\ContentSystem\Resolver\Struct\ResolvedData;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * The LayoutRefinery orchestrates layout refinement.
 *
 * Like a refinery that processes raw materials through multiple stages,
 * this service refines content layouts through multiple LayoutRefiners,
 * progressively transforming them into polished, concrete structures.
 *
 * Refiners execute in priority order (tagged iterator handles sorting).
 * Each refiner runs exactly once in a single sequential pass.
 *
 * IMPORTANT: The refinery does NOT iterate. Recursive placeholder resolution
 * is not supported. Extension refiners that add placeholders must resolve
 * them to final values, not to other placeholder patterns.
 *
 * @internal
 */
#[Package('discovery')]
class LayoutRefinery
{
    /**
     * @param iterable<LayoutRefinerInterface> $refiners
     */
    public function __construct(
        private readonly iterable $refiners
    ) {
    }

    /**
     * Refine a content layout through all registered refiners.
     *
     * @param ContentElement $layout Layout with placeholders
     * @param ResolvedData $resolvedData Entity IDs and parameters from routing
     * @param ChannelContext $context Sales channel context
     *
     * @return ContentElement Refined layout ready for hydration
     */
    public function refine(
        ContentElement $layout,
        ResolvedData $resolvedData,
        ChannelContext $context
    ): ContentElement {
        // Execute refiners in priority order (higher priority first)
        foreach ($this->refiners as $refiner) {
            $layout = $refiner->refine($layout, $resolvedData, $context);
        }

        return $layout;
    }
}
