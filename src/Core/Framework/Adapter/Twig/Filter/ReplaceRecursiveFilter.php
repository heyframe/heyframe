<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Adapter\Twig\Filter;

use HeyFrame\Core\Framework\Log\Package;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

#[Package('framework')]
class ReplaceRecursiveFilter extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('replace_recursive', $this->replaceRecursive(...)),
        ];
    }

    /**
     * @param array<mixed> ...$params
     *
     * @return array<mixed>
     */
    public function replaceRecursive(array ...$params): array
    {
        return array_replace_recursive(...$params);
    }
}
