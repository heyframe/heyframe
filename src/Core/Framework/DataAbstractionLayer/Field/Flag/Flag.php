<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag;

use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
abstract class Flag
{
    /**
     * Returns a readable name for the flag
     */
    abstract public function parse(): \Generator;
}
