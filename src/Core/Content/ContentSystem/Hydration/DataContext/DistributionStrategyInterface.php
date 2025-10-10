<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Hydration\DataContext;

use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('discovery')]
interface DistributionStrategyInterface
{
    public function supports(string $distribution): bool;

    /**
     * @param array<int, array<string, mixed>> $consumers
     * @param array<string, mixed> $config
     *
     * @return array<int, mixed>
     */
    public function distribute(mixed $data, array $consumers, array $config): array;
}
