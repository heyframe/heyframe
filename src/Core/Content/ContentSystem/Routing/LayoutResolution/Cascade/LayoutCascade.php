<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Routing\LayoutResolution\Cascade;

use HeyFrame\Core\Content\ContentSystem\Layout\Entity\ContentLayoutAssignmentEntity;
use HeyFrame\Core\Content\ContentSystem\Routing\IdResolution\Struct\ResolvedData;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * Encapsulates cascade strategy using polymorphism.
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

    public function isEmpty(): bool
    {
        return empty($this->steps);
    }

    public function count(): int
    {
        return \count($this->steps);
    }
}
