<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Layout\Element\Context;

use HeyFrame\Core\Content\ContentSystem\Hydration\DataContext\ContextType;
use HeyFrame\Core\Content\ContentSystem\Hydration\DataContext\Distribution\Config\DistributionConfig;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('discovery')]
class ContextProvider
{
    public function __construct(
        public readonly ContextType $type,
        public readonly DistributionConfig $config
    ) {
    }

    public function getDistribution(): DistributionConfig
    {
        return $this->config;
    }
}
