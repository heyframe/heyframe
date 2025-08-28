<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag;

use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class Since extends Flag
{
    public function __construct(private readonly string $since)
    {
    }

    public function parse(): \Generator
    {
        yield 'since' => $this->since;
    }

    public function getSince(): string
    {
        return $this->since;
    }
}
