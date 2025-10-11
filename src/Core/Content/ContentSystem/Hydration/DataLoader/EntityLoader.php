<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Hydration\DataLoader;

use HeyFrame\Core\Content\ContentSystem\Layout\Element\ContentElement;
use HeyFrame\Core\Content\ContentSystem\Layout\Element\DataRequirement\DataRequirement;
use HeyFrame\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\ChannelEntity;
use HeyFrame\Core\System\Channel\Entity\ChannelDefinitionInstanceRegistry;
use HeyFrame\Core\System\Channel\Exception\ChannelRepositoryNotFoundException;

/**
 * @phpstan-type EntityLoaderConfig array{
 *   entity: string,
 *   property?: string,
 *   associations?: list<string>
 * }
 *
 * @internal
 */
#[Package('discovery')]
readonly class EntityLoader implements ContentDataLoaderInterface
{
    public function __construct(
        private ChannelDefinitionInstanceRegistry $salesChannelDefinitionRegistry,
        private DefinitionInstanceRegistry $definitionRegistry
    ) {
    }

    public static function getRequirementType(): string
    {
        return 'entity';
    }

    /**
     * @param DataRequirement $requirement Expects $requirement->config to be EntityLoaderConfig
     */
    public function load(
        ContentElement $element,
        DataRequirement $requirement,
        ChannelContext $context
    ): ChannelEntity|Entity|null {
        $entityType = $requirement->config['entity'] ?? null;

        if (!\is_string($entityType)) {
            return null;
        }

        $propertyName = $requirement->config['property'] ?? $entityType;
        $entityId = $element->getProperty($propertyName);

        if ($entityId === null) {
            return null;
        }

        if (!\is_string($entityId)) {
            return null;
        }

        $associations = $requirement->config['associations'] ?? [];
        if (!\is_array($associations)) {
            $associations = [];
        }

        return $this->loadEntity($entityType, $entityId, $associations, $context);
    }

    /**
     * @param list<string> $associations
     */
    private function loadEntity(
        string $entityName,
        string $entityId,
        array $associations,
        ChannelContext $context
    ): ChannelEntity|Entity|null {
        $criteria = new Criteria([$entityId]);

        foreach ($associations as $association) {
            if (\is_string($association)) {
                $criteria->addAssociation($association);
            }
        }

        try {
            $salesChannelRepository = $this->salesChannelDefinitionRegistry->getChannelRepository($entityName);
            $result = $salesChannelRepository->search($criteria, $context);
        } catch (ChannelRepositoryNotFoundException) {
            $repository = $this->definitionRegistry->getRepository($entityName);
            $result = $repository->search($criteria, $context->getContext());
        }

        return $result->first();
    }
}
