<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Hydration;

use HeyFrame\Core\Content\ContentSystem\ContentSystemException;
use HeyFrame\Core\Content\ContentSystem\Element\Runtime\ContentElement;
use HeyFrame\Core\Content\ContentSystem\Hydration\DataLoader\ContentDataLoaderInterface;
use HeyFrame\Core\Content\ContentSystem\TypeRegistry\ContentElementTypeRegistry;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * Schema-driven hydrator for service category elements (entities and runtime data).
 * Delegates to registered data loaders based on data requirements.
 *
 * @internal
 */
#[Package('discovery')]
class ServiceElementHydrator extends AbstractContentElementHydrator
{
    /**
     * @param ServiceLocator<ContentDataLoaderInterface> $dataLoaders
     */
    public function __construct(
        private readonly ContentElementTypeRegistry $typeRegistry,
        private readonly ServiceLocator $dataLoaders
    ) {
    }

    public function supports(ContentElement $element): bool
    {
        // First check element's embedded category (self-describing)
        $category = $element->getCategory();

        // Fallback to TypeRegistry for backward compatibility
        if ($category === null) {
            $category = $this->typeRegistry->getCategory($element->getType());
        }

        return $category === 'service';
    }

    public function hydrate(ContentElement $element, ChannelContext $context): void
    {
        // Get data requirements from element (self-describing)
        $dataRequirements = $element->getDataRequirements();

        // Fallback to schema for backward compatibility
        if ($dataRequirements === []) {
            $schema = $this->typeRegistry->getSchema($element->getType());
            $dataRequirements = $schema['data_requirements'] ?? [];
        }

        if (!\is_array($dataRequirements) || $dataRequirements === []) {
            return; // No data requirements
        }

        // Process each data requirement
        foreach ($dataRequirements as $key => $requirement) {
            $this->hydrateRequirement($element, $key, $requirement, $context);
        }
    }

    /**
     * Hydrate a single data requirement using appropriate data loader.
     *
     * @param array<string, mixed> $requirement
     */
    private function hydrateRequirement(
        ContentElement $element,
        string $key,
        mixed $requirement,
        ChannelContext $context
    ): void {
        // Validate requirement is array
        if (!\is_array($requirement)) {
            throw ContentSystemException::invalidDataRequirement(get_debug_type($requirement));
        }

        // Get requirement type (what data is needed)
        $requirementType = $requirement['type'] ?? null;
        if ($requirementType === null || !\is_string($requirementType)) {
            return; // No type specified
        }

        // Check if loader for this requirement type is registered
        if (!$this->dataLoaders->has($requirementType)) {
            throw ContentSystemException::dataLoaderNotRegistered($requirementType, $element->getType(), $element->getId());
        }

        // Get the data loader
        $loader = $this->dataLoaders->get($requirementType);

        // Get property name to store result (defaults to requirement key)
        $propertyName = $requirement['property'] ?? $key;

        // Call loader to get data
        $data = $loader->load($element, $requirement, $context);

        // Store result in element property
        if ($data !== null) {
            $element->setProperty($propertyName, $data);
        }
    }
}
