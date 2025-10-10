<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Hydration\DataContext\Distribution;

use HeyFrame\Core\Content\ContentSystem\Hydration\DataContext\DistributionStrategyInterface;
use HeyFrame\Core\Framework\Log\Package;

/**
 * Signals DataContextResolver to clone template element per collection item.
 * Unlike other strategies, this modifies element tree structure.
 *
 * @internal
 */
#[Package('discovery')]
class IteratorDistributor implements DistributionStrategyInterface
{
    public function supports(string $distribution): bool
    {
        return $distribution === 'iterator';
    }

    /**
     * Element cloning handled by DataContextResolver, not here.
     *
     * @return array<int, mixed>
     */
    public function distribute(mixed $data, array $consumers, array $config): array
    {
        if (!\is_array($data)) {
            return [];
        }

        return array_values($data);
    }
}
