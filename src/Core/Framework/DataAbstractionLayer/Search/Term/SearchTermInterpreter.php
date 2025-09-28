<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Search\Term;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class SearchTermInterpreter
{
    /**
     * @internal
     */
    public function __construct(
        private readonly TokenizerInterface $tokenizer,
    ) {
    }

    public function interpret(string $term, Context $context): SearchPattern
    {
        /** @phpstan-ignore arguments.count (This ignore should be removed when the deprecated method signature is updated) */
        $terms = $this->tokenizer->tokenize($term);

        $pattern = new SearchPattern(new SearchTerm($term));

        if (\count($terms) === 1) {
            return $pattern;
        }

        foreach ($terms as $part) {
            $percent = mb_strlen($part) / mb_strlen($term);
            $pattern->addTerm(new SearchTerm($part, $percent));
        }

        return $pattern;
    }
}
