<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Layout\Element\Visitor;

use HeyFrame\Core\Content\ContentSystem\Layout\Element\ContentElement;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('discovery')]
interface ElementVisitor
{
    public function enter(ContentElement $element): void;

    public function leave(ContentElement $element): void;
}
