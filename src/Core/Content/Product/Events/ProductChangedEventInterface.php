<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Events;

use HeyFrame\Core\Framework\Log\Package;

#[Package('inventory')]
interface ProductChangedEventInterface
{
    /**
     * @return list<string>
     */
    public function getIds(): array;
}
