<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Routing\LayoutResolution\Cascade;

use HeyFrame\Core\Content\ContentSystem\Routing\IdResolution\Struct\ResolvedData;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\PartialEntity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * @internal
 */
#[Package('discovery')]
final readonly class DefaultLayoutStep implements CascadeStepInterface
{
    public function buildFilters(ResolvedData $data, ChannelContext $context): array
    {
        return [
            new MultiFilter(MultiFilter::CONNECTION_AND, [
                new EqualsFilter('entityType', null),
                new EqualsFilter('entityId', null),
            ]),
        ];
    }

    public function resolve(EntityCollection $assignments, ResolvedData $data, ChannelContext $context): ?string
    {
        /** @var PartialEntity|null $assignment */
        $assignment = $assignments->filter(
            fn (PartialEntity $a) => $a->get('entityType') === null
        )->first();

        if ($assignment && $assignment->get('layoutId')) {
            return $assignment->get('layoutId');
        }

        return null;
    }
}
