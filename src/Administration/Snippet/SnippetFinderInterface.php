<?php declare(strict_types=1);

namespace HeyFrame\Administration\Snippet;

use HeyFrame\Core\Framework\Log\Package;

#[Package('discovery')]
interface SnippetFinderInterface
{
    /**
     * @return array<string, mixed>
     */
    public function findSnippets(string $locale): array;
}
