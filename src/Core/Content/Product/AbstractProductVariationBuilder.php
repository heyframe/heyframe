<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product;

use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\Log\Package;

#[Package('inventory')]
abstract class AbstractProductVariationBuilder
{
    abstract public function getDecorated(): AbstractProductVariationBuilder;

    abstract public function build(Entity $product): void;
}
