<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Compilation;

use HeyFrame\Core\Content\ContentSystem\Element\Runtime\ContentElement;
use HeyFrame\Core\Content\ContentSystem\Resolver\Struct\ResolvedData;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * Interface for layout refiners that transform content layouts.
 *
 * Refiners execute in priority order (higher priority first) in a single
 * sequential pass. Each refiner runs exactly once per layout.
 *
 * Priority Guidelines:
 * - PlaceholderResolutionRefiner runs LAST at priority 0
 * - Extension refiners should use priority > 0
 * - Refiners that add placeholders MUST resolve them to final values,
 *   not to other placeholder patterns (recursive resolution not supported)
 *
 * @internal
 */
#[Package('discovery')]
interface LayoutRefinerInterface
{
    /**
     * Refines a content layout.
     *
     * @param ContentElement $layout The layout to refine
     * @param ResolvedData $resolvedData Entity IDs and parameters from routing
     * @param ChannelContext $context Sales channel context
     *
     * @return ContentElement The refined layout
     */
    public function refine(
        ContentElement $layout,
        ResolvedData $resolvedData,
        ChannelContext $context
    ): ContentElement;
}
