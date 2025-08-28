<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag;

use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class Required extends Flag
{
    public function parse(): \Generator
    {
        yield 'required' => true;
    }
}
