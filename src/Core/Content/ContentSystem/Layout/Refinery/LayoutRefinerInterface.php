<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Layout\Refinery;

use HeyFrame\Core\Content\ContentSystem\Layout\Element\ContentElement;
use HeyFrame\Core\Content\ContentSystem\Routing\IdResolution\Struct\ResolvedData;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * Refines content layouts in priority order (single sequential pass).
 *
 * Priority: PlaceholderResolutionRefiner=0 (last), extensions>0.
 * Refiners adding placeholders must resolve to final values (no recursion).
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
