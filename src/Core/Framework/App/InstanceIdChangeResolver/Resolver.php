<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\InstanceIdChangeResolver;

use HeyFrame\Core\Framework\App\AppException;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('framework')]
readonly class Resolver
{
    /**
     * @param AbstractInstanceIdChangeStrategy[] $strategies
     */
    public function __construct(
        private iterable $strategies
    ) {
    }

    public function resolve(string $strategyName, Context $context): void
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->getName() === $strategyName) {
                $strategy->resolve($context);

                return;
            }
        }

        throw AppException::instanceIdChangeResolveStrategyNotFound($strategyName);
    }

    /**
     * @return array<string>
     */
    public function getAvailableStrategies(): array
    {
        $strategies = [];

        foreach ($this->strategies as $strategy) {
            $strategies[$strategy->getName()] = $strategy->getDescription();
        }

        return $strategies;
    }
}
