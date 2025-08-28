<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag;

use HeyFrame\Core\Framework\Log\Package;

/**
 * The value is computed by indexer or external systems and
 * cannot be written using the DAL.
 */
#[Package('framework')]
class Computed extends Flag
{
    public function parse(): \Generator
    {
        yield 'computed' => true;
    }
}
