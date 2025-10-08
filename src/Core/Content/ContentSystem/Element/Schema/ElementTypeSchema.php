<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Element\Schema;

use HeyFrame\Core\Framework\Log\Package;

/**
 * Value object representing a complete content element type definition.
 *
 * Element types are defined declaratively via JSON schemas and loaded
 * from HeyFrame core, plugins, and apps. This immutable object holds
 * all metadata needed for generic processing without type-specific code.
 *
 * @internal
 */
#[Package('discovery')]
final readonly class ElementTypeSchema
{
    /**
     * @param string $name Unique type name (e.g., 'Sw:Product:Card')
     * @param string $category Element category (container, static, entity, service)
     * @param string $label Human-readable label
     * @param string $description Element description
     * @param array<string, mixed> $propertiesSchema JSON Schema for properties validation
     * @param array<string, mixed> $dataRequirements Data transformation specifications
     * @param array<string, mixed> $slotsSchema Schema for child element slots
     * @param array<string, mixed> $metadata Additional metadata
     */
    public function __construct(
        private string $name,
        private string $category,
        private string $label,
        private string $description,
        private array $propertiesSchema = [],
        private array $dataRequirements = [],
        private array $slotsSchema = [],
        private array $metadata = []
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return array<string, mixed>
     */
    public function getPropertiesSchema(): array
    {
        return $this->propertiesSchema;
    }

    /**
     * @return array<string, mixed>
     */
    public function getDataRequirements(): array
    {
        return $this->dataRequirements;
    }

    /**
     * @return array<string, mixed>
     */
    public function getSlotsSchema(): array
    {
        return $this->slotsSchema;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Create ElementTypeSchema from array definition.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['type'],
            category: $data['category'],
            label: $data['label'] ?? $data['type'],
            description: $data['description'] ?? '',
            propertiesSchema: $data['properties_schema'] ?? [],
            dataRequirements: $data['data_requirements'] ?? [],
            slotsSchema: $data['slots_schema'] ?? [],
            metadata: $data['metadata'] ?? []
        );
    }

    /**
     * Convert to array representation.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'type' => $this->name,
            'category' => $this->category,
            'label' => $this->label,
            'description' => $this->description,
        ];

        if ($this->propertiesSchema !== []) {
            $data['properties_schema'] = $this->propertiesSchema;
        }

        if ($this->dataRequirements !== []) {
            $data['data_requirements'] = $this->dataRequirements;
        }

        if ($this->slotsSchema !== []) {
            $data['slots_schema'] = $this->slotsSchema;
        }

        if ($this->metadata !== []) {
            $data['metadata'] = $this->metadata;
        }

        return $data;
    }
}
