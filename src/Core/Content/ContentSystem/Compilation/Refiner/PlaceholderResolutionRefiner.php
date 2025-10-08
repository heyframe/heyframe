<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Compilation\Refiner;

use HeyFrame\Core\Content\ContentSystem\Compilation\LayoutRefinerInterface;
use HeyFrame\Core\Content\ContentSystem\Element\Runtime\ContentElement;
use HeyFrame\Core\Content\ContentSystem\Resolver\Struct\ResolvedData;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * Refines layouts by resolving placeholders in a single pass.
 *
 * Replaces {{variable}} patterns with values from ResolvedData,
 * transforming abstract layouts into concrete structures.
 *
 * This refiner runs LAST (priority 0) to allow extension refiners
 * to add their own placeholders before final resolution.
 *
 * IMPORTANT: Recursive/circular placeholders are NOT supported.
 * Placeholder resolution runs exactly once. If a resolved value contains
 * placeholder syntax (e.g., {{a}} resolves to "{{b}}"), the inner
 * placeholder will NOT be resolved and remains as literal text.
 *
 * Extension refiners that add placeholders must resolve them to final
 * values, not to other placeholder patterns.
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
        $layoutArray = $layout->toArray();
        $json = \json_encode($layoutArray);

        if ($json === false) {
            return $layout;
        }

        $values = $resolvedData->getValues();

        foreach ($values as $key => $value) {
            if (\is_scalar($value)) {
                $placeholder = '{{' . $key . '}}';
                $json = \str_replace($placeholder, (string) $value, $json);
            }
        }

        $filled = \json_decode($json, true);

        if ($filled === null) {
            return $layout;
        }

        return ContentElement::fromArray($filled);
    }
}
