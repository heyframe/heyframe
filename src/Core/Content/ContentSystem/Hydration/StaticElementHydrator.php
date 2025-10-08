<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Hydration;

use HeyFrame\Core\Content\ContentSystem\ContentSystemException;
use HeyFrame\Core\Content\ContentSystem\Element\Runtime\ContentElement;
use HeyFrame\Core\Content\ContentSystem\TypeRegistry\ContentElementTypeRegistry;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Schema-driven hydrator for static category elements (translations, snippets, containers).
 *
 * @internal
 */
#[Package('discovery')]
class StaticElementHydrator extends AbstractContentElementHydrator
{
    public function __construct(
        private readonly ContentElementTypeRegistry $typeRegistry,
        private readonly TranslatorInterface $translator
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

        return $category === 'static';
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
     * Hydrate a single data requirement.
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

        $type = $requirement['type'] ?? 'translation';

        match ($type) {
            'translation' => $this->hydrateTranslation($element, $key, $requirement, $context),
            default => null,
        };
    }

    /**
     * Hydrate translation requirement.
     *
     * @param array<string, mixed> $requirement
     */
    private function hydrateTranslation(
        ContentElement $element,
        string $key,
        array $requirement,
        ChannelContext $context
    ): void {
        // Get source property name
        $sourcePath = $requirement['source'] ?? $key;

        // Resolve translation key from element properties
        $translationKey = $this->resolvePropertyPath($element, $sourcePath);

        if ($translationKey === null || !\is_string($translationKey)) {
            return;
        }

        // Get translation domain
        $domain = $requirement['domain'] ?? 'storefront';

        // Translate
        $translated = $this->translator->trans($translationKey, [], $domain);

        // Get property name to store result (defaults to requirement key)
        $propertyName = $requirement['property'] ?? $key;

        // Store in element property
        $element->setProperty($propertyName, $translated);
    }

    /**
     * Resolve a property path like "text" or "config.text".
     *
     * @return mixed Property value or null if not found
     */
    private function resolvePropertyPath(ContentElement $element, string $path): mixed
    {
        // Simple property access (no nested paths for now)
        return $element->getProperty($path);
    }
}
