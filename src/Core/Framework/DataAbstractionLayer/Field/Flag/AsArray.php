<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag;

use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class AsArray extends Flag
{
    public function parse(): \Generator
    {
        yield 'as_array' => true;
    }
}
