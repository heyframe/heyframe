<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Hydration\DataLoader;

use HeyFrame\Core\Content\ContentSystem\Layout\Element\ContentElement;
use HeyFrame\Core\Content\ContentSystem\Layout\Element\DataRequirement\DataRequirement;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * @internal
 */
#[Package('discovery')]
interface ContentDataLoaderInterface
{
    /**
     * @return string Requirement type identifier (e.g., 'entity', 'product_listing')
     */
    public static function getRequirementType(): string;

    public function load(
        ContentElement $element,
        DataRequirement $requirement,
        ChannelContext $context
    ): mixed;
}
