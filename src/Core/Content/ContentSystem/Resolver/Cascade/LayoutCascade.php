<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Resolver\Cascade;

use HeyFrame\Core\Content\ContentSystem\ContentLayoutAssignment\ContentLayoutAssignmentEntity;
use HeyFrame\Core\Content\ContentSystem\Resolver\Struct\ResolvedData;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * Layout cascade with resolution behavior.
 *
 * Encapsulates the cascade resolution strategy, eliminating procedural
 * conditionals from LayoutResolver.
 *
 * Steps are resolved in order (array position = priority).
 * Returns first matching layout ID.
 *
 * @internal
 */
#[Package('discovery')]
final readonly class LayoutCascade
{
    /**
     * @param array<CascadeStepInterface> $steps
     */
    public function __construct(
        private array $steps
    ) {
    }

    /**
     * Create cascade from configuration array.
     *
     * @param array<int, array<string, mixed>>|null $config
     */
    public static function fromArray(?array $config, CascadeStepFactory $factory): ?self
    {
        if ($config === null || empty($config)) {
            return null;
        }

        $steps = [];
        foreach ($config as $stepConfig) {
            $steps[] = $factory->create($stepConfig);
        }

        return new self($steps);
    }

    /**
     * Build all DAL filters for loading assignments.
     *
     * Delegates to each step to build its filters.
     *
     * @return array<MultiFilter>
     */
    public function buildFilters(ResolvedData $data, ChannelContext $context): array
    {
        $filters = [];

        foreach ($this->steps as $step) {
            $stepFilters = $step->buildFilters($data, $context);
            $filters = \array_merge($filters, $stepFilters);
        }

        return $filters;
    }

    /**
     * Resolve layout ID from loaded assignments.
     *
     * Iterates steps in order (position = priority).
     * Returns first matching layout ID.
     *
     * @param EntityCollection<ContentLayoutAssignmentEntity> $assignments
     */
    public function resolve(EntityCollection $assignments, ResolvedData $data, ChannelContext $context): ?string
    {
        foreach ($this->steps as $step) {
            $layoutId = $step->resolve($assignments, $data, $context);
            if ($layoutId !== null) {
                return $layoutId;
            }
        }

        return null;
    }

    /**
     * Check if cascade is empty.
     */
    public function isEmpty(): bool
    {
        return empty($this->steps);
    }

    /**
     * Get number of steps.
     */
    public function count(): int
    {
        return \count($this->steps);
    }
}
