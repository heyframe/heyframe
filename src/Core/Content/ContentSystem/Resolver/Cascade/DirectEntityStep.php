<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Resolver\Cascade;

use HeyFrame\Core\Content\ContentSystem\Resolver\Struct\ResolvedData;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\PartialEntity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * Direct entity step - resolves layout by entity type and ID.
 *
 * Example: ['entity' => 'product']
 *
 * Looks for entity ID in resolved data using:
 * 1. {entityType}_id (e.g., 'product_id')
 * 2. {entityType} (e.g., 'product')
 *
 * Matches assignments with:
 * - entityType = 'product'
 * - entityId = [resolved ID]
 *
 * @internal
 */
#[Package('discovery')]
final readonly class DirectEntityStep implements CascadeStepInterface
{
    public function __construct(
        private string $entityType
    ) {
    }

    public function buildFilters(ResolvedData $data, ChannelContext $context): array
    {
        $entityId = $this->getEntityId($data);

        if ($entityId === null) {
            return [];
        }

        return [
            new MultiFilter(MultiFilter::CONNECTION_AND, [
                new EqualsFilter('entityType', $this->entityType),
                new EqualsFilter('entityId', $entityId),
            ]),
        ];
    }

    public function resolve(EntityCollection $assignments, ResolvedData $data, ChannelContext $context): ?string
    {
        $entityId = $this->getEntityId($data);

        if ($entityId === null) {
            return null;
        }

        /** @var PartialEntity|null $assignment */
        $assignment = $assignments->filter(
            fn (PartialEntity $a) => $a->get('entityType') === $this->entityType
                && $a->get('entityId') === $entityId
        )->first();

        if ($assignment && $assignment->get('layoutId')) {
            return $assignment->get('layoutId');
        }

        return null;
    }

    /**
     * Get entity ID from resolved data.
     * Tries {entityType}_id first, then {entityType}.
     */
    private function getEntityId(ResolvedData $data): ?string
    {
        return $data->getEntityId($this->entityType . '_id')
            ?? $data->getEntityId($this->entityType);
    }
}
