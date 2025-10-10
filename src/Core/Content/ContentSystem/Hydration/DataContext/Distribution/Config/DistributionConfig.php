<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Hydration\DataContext\Distribution\Config;

use HeyFrame\Core\Content\ContentSystem\Hydration\DataContext\DistributionStrategy;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('discovery')]
interface DistributionConfig
{
    public function getStrategy(): DistributionStrategy;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self;
}
