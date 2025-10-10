<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Layout\Refinery\Refiner;

use HeyFrame\Core\Content\ContentSystem\Layout\Element\ContentElement;
use HeyFrame\Core\Content\ContentSystem\Layout\Refinery\LayoutRefinerInterface;
use HeyFrame\Core\Content\ContentSystem\Routing\IdResolution\Struct\ResolvedData;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * Resolves {{variable}} placeholders in single pass (priority 0). Recursive resolution not supported.
 *
 * @internal
 */
#[Package('discovery')]
class PlaceholderResolutionRefiner implements LayoutRefinerInterface
{
    public function refine(
        ContentElement $layout,
        ResolvedData $resolvedData,
        ChannelContext $context
    ): ContentElement {
        $layout->replacePlaceholders($resolvedData);

        return $layout;
    }
}
