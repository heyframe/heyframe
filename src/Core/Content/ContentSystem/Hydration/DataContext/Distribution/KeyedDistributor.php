<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Hydration\DataContext\Distribution;

use HeyFrame\Core\Content\ContentSystem\Hydration\DataContext\DistributionStrategyInterface;
use HeyFrame\Core\Framework\Log\Package;

/**
 * Distributes collection items by key matching consumer 'data_key'. Missing keys → null.
 *
 * @internal
 */
#[Package('discovery')]
class KeyedDistributor implements DistributionStrategyInterface
{
    public function supports(string $distribution): bool
    {
        return $distribution === 'keyed';
    }

    /**
     * @return array<int, mixed>
     */
    public function distribute(mixed $data, array $consumers, array $config): array
    {
        if (!\is_array($data)) {
            return array_fill(0, \count($consumers), null);
        }

        $result = [];
        foreach ($consumers as $index => $consumer) {
            $dataKey = $consumer['data_key'] ?? null;

            if ($dataKey === null) {
                $result[$index] = null;
                continue;
            }

            $result[$index] = $data[$dataKey] ?? null;
        }

        return $result;
    }
}
