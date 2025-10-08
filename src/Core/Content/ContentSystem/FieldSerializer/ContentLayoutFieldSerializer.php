<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\FieldSerializer;

use HeyFrame\Core\Content\ContentSystem\ContentSystemException;
use HeyFrame\Core\Content\ContentSystem\Element\Runtime\ContentElement;
use HeyFrame\Core\Content\ContentSystem\Field\ContentLayoutField;
use HeyFrame\Core\Content\ContentSystem\TypeRegistry\ContentElementTypeRegistry;
use HeyFrame\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Field;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StorageAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldSerializer\AbstractFieldSerializer;
use HeyFrame\Core\Framework\DataAbstractionLayer\Write\DataStack\KeyValuePair;
use HeyFrame\Core\Framework\DataAbstractionLayer\Write\EntityExistence;
use HeyFrame\Core\Framework\DataAbstractionLayer\Write\WriteParameterBag;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Util\Json;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Serializes ContentElement trees to/from JSON with schema metadata enrichment.
 * Denormalizes category and data_requirements at write-time for self-describing storage.
 *
 * @internal
 */
#[Package('discovery')]
class ContentLayoutFieldSerializer extends AbstractFieldSerializer
{
    public function __construct(
        ValidatorInterface $validator,
        DefinitionInstanceRegistry $definitionRegistry,
        private readonly ContentElementTypeRegistry $typeRegistry
    ) {
        parent::__construct($validator, $definitionRegistry);
    }

    public function encode(
        Field $field,
        EntityExistence $existence,
        KeyValuePair $data,
        WriteParameterBag $parameters
    ): \Generator {
        if (!$field instanceof StorageAware) {
            throw ContentSystemException::invalidFieldType(StorageAware::class, $field::class);
        }

        $this->validateIfNeeded($field, $existence, $data, $parameters);

        $value = $data->getValue();

        // Convert ContentElement to array if needed
        if ($value instanceof ContentElement) {
            $value = $this->elementToArray($value);
        }

        if ($value !== null) {
            $value = Json::encode($value);
        }

        yield $field->getStorageName() => $value;
    }

    public function decode(Field $field, mixed $value): ?ContentElement
    {
        if (!$field instanceof ContentLayoutField) {
            throw ContentSystemException::invalidFieldType(ContentLayoutField::class, $field::class);
        }

        if ($value === null) {
            return null;
        }

        // Decode JSON string to array
        if (\is_string($value)) {
            $value = json_decode($value, true, 512, \JSON_THROW_ON_ERROR);
        }

        if (!\is_array($value)) {
            throw ContentSystemException::invalidFieldValueType('layout', 'array', \gettype($value));
        }

        // Convert array to ContentElement tree (recursive)
        return $this->decodeElement($value);
    }

    protected function getConstraints(Field $field): array
    {
        $constraints = [
            new Type('array'),
        ];

        if ($field->is(Required::class)) {
            $constraints[] = new NotBlank();
        }

        return $constraints;
    }

    /**
     * Recursively decodes an element array into a ContentElement object.
     *
     * Processes nested slots to create the full element tree.
     *
     * @param array<string, mixed> $data
     */
    private function decodeElement(array $data): ContentElement
    {
        // Recursively convert slots
        if (isset($data['slots']) && \is_array($data['slots'])) {
            foreach ($data['slots'] as $slotName => $slotElements) {
                if (!\is_array($slotElements)) {
                    continue;
                }

                $data['slots'][$slotName] = array_map(
                    fn ($element) => \is_array($element) ? $this->decodeElement($element) : $element,
                    $slotElements
                );
            }
        }

        return ContentElement::fromArray($data);
    }

    /**
     * Recursively converts ContentElement tree to array with enrichment.
     *
     * Processes nested slots for complete serialization.
     * Enriches elements with schema metadata to make them self-describing.
     */
    private function elementToArray(ContentElement $element): array
    {
        $array = $element->toArray();

        // Enrich element with schema metadata (write-time denormalization)
        $array = $this->enrichElement($array);

        // Recursively convert slots
        if (isset($array['slots']) && \is_array($array['slots'])) {
            foreach ($array['slots'] as $slotName => $slotElements) {
                if (!\is_array($slotElements)) {
                    continue;
                }

                $array['slots'][$slotName] = array_map(
                    fn ($el) => $el instanceof ContentElement ? $this->elementToArray($el) : $el,
                    $slotElements
                );
            }
        }

        return $array;
    }

    /**
     * Enrich element array with schema metadata.
     *
     * Denormalizes category and data_requirements from schema into element JSON
     * so elements are self-describing in the database. This enables:
     * 1. Unregistered types to work (inline metadata)
     * 2. No schema lookup needed during read/hydration
     * 3. Self-contained elements independent of registry state
     *
     * @param array<string, mixed> $element
     *
     * @return array<string, mixed>
     */
    private function enrichElement(array $element): array
    {
        $type = $element['type'] ?? null;

        if ($type === null || !\is_string($type)) {
            return $element;
        }

        // Only enrich if type is registered
        if (!$this->typeRegistry->hasType($type)) {
            return $element;
        }

        // Get schema from registry
        $schema = $this->typeRegistry->getSchema($type);

        // Add category if not already present
        if (!isset($element['category']) && isset($schema['category'])) {
            $element['category'] = $schema['category'];
        }

        // Add data_requirements if not already present
        if (!isset($element['data_requirements']) && isset($schema['data_requirements'])) {
            $element['data_requirements'] = $schema['data_requirements'];
        }

        return $element;
    }
}
