<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Hydration\DataLoader;

use HeyFrame\Core\Content\ContentSystem\Layout\Element\ContentElement;
use HeyFrame\Core\Content\ContentSystem\Layout\Element\DataRequirement\DataRequirement;
use HeyFrame\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Entity\ChannelDefinitionInstanceRegistry;
use HeyFrame\Core\System\Channel\Exception\ChannelRepositoryNotFoundException;

/**
 * @phpstan-type EntityCollectionLoaderConfig array{
 *   entity: string,
 *   property?: string,
 *   associations?: list<string>
 * }
 *
 * @internal
 */
#[Package('discovery')]
readonly class EntityCollectionLoader implements ContentDataLoaderInterface
{
    public function __construct(
        private ChannelDefinitionInstanceRegistry $salesChannelDefinitionRegistry,
        private DefinitionInstanceRegistry $definitionRegistry
    ) {
    }

    public static function getRequirementType(): string
    {
        return 'entity_collection';
    }

    /**
     * @param DataRequirement $requirement Expects $requirement->config to be EntityCollectionLoaderConfig
     *
     * @return list<covariant Entity>|null
     */
    public function load(
        ContentElement $element,
        DataRequirement $requirement,
        ChannelContext $context
    ): ?array {
        $entityType = $requirement->config['entity'] ?? null;

        if (!\is_string($entityType)) {
            return null;
        }

        $propertyName = $requirement->config['property'] ?? $entityType . 'Ids';
        $entityIds = $element->getProperty($propertyName);

        if ($entityIds === null) {
            return [];
        }

        if (!\is_array($entityIds)) {
            return null;
        }

        $entityIds = array_values(array_filter($entityIds, static fn ($id) => \is_string($id)));

        if (empty($entityIds)) {
            return null;
        }

        $associations = $requirement->config['associations'] ?? [];
        if (!\is_array($associations)) {
            $associations = [];
        }

        return $this->loadEntities($entityType, $entityIds, $associations, $context);
    }

    /**
     * @param list<string> $entityIds
     * @param list<string> $associations
     *
     * @return list<covariant Entity>
     */
    private function loadEntities(
        string $entityName,
        array $entityIds,
        array $associations,
        ChannelContext $context
    ): array {
        $criteria = new Criteria($entityIds);

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

        return array_values($result->getEntities()->getElements());
    }
}
