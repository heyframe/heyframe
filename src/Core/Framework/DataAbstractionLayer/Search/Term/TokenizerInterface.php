<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Search\Term;

use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
interface TokenizerInterface
{
    /**
     * @return list<string>
     */
    public function tokenize(string $string, ?int $tokenMinimumLength = null): array;
}
