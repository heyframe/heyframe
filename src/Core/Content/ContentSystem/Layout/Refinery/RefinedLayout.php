<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Layout\Refinery;

use HeyFrame\Core\Content\ContentSystem\Layout\Element\ContentElement;
use HeyFrame\Core\Content\ContentSystem\Layout\Entity\ContentLayoutEntity;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @final
 */
#[Package('discovery')]
class RefinedLayout
{
    /**
     * @internal
     */
    public function __construct(
        public readonly ContentLayoutEntity $layoutEntity,
        public readonly ContentElement $rootElement,
    ) {
    }
}
