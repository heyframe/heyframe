<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Layout\Refinery;

use HeyFrame\Core\Content\ContentSystem\Layout\Element\ContentElement;
use HeyFrame\Core\Content\ContentSystem\Routing\IdResolution\Struct\ResolvedData;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * Orchestrates layout refinement through sequential refiners.
 *
 * IMPORTANT: Single-pass only. Recursive placeholder resolution not supported.
 * Extension refiners adding placeholders must resolve to final values.
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

    public function refine(
        ContentElement $layout,
        ResolvedData $resolvedData,
        ChannelContext $context
    ): ContentElement {
        foreach ($this->refiners as $refiner) {
            $layout = $refiner->refine($layout, $resolvedData, $context);
        }

        return $layout;
    }
}
