<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Search\Term;

interface TokenizerInterface
{
    /**
     * @deprecated tag:v6.8.0 - reason:new-optional-parameter - parameter $tokenMinimumLength will be added
     *
     * @return list<string>
     */
    public function tokenize(string $string/* , ?int $tokenMinimumLength = null */): array;
}
