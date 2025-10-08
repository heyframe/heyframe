<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Element\Definition;

use HeyFrame\Core\Content\ContentSystem\ContentSystemException;
use HeyFrame\Core\Framework\Log\Package;

/**
 * Validates element type schemas for correctness and completeness.
 *
 * Ensures element type definitions meet requirements before registration.
 * Validates structure, required fields, and category-specific rules.
 *
 * @internal
 */
#[Package('discovery')]
class SchemaValidator
{
    private const VALID_CATEGORIES = ['static', 'service'];
    private const VALID_REQUIREMENT_TYPES = ['entity', 'translation', 'service', 'aggregation'];

    /**
     * Validate an element type schema definition.
     *
     * @param array<string, mixed> $schema Raw schema data
     *
     * @throws ContentSystemException If validation fails
     */
    public function validate(array $schema): void
    {
        $this->validateRequiredFields($schema);
        $this->validateCategory($schema);
        $this->validateType($schema);
        $this->validateDataRequirements($schema);
        $this->validatePropertiesSchema($schema);
        $this->validateSlotsSchema($schema);
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function validateRequiredFields(array $schema): void
    {
        $required = ['type', 'category'];

        foreach ($required as $field) {
            if (!isset($schema[$field]) || $schema[$field] === '') {
                throw ContentSystemException::elementSchemaInvalid(
                    $schema['type'] ?? 'unknown',
                    \sprintf('Missing required field: %s', $field)
                );
            }
        }
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function validateCategory(array $schema): void
    {
        $category = $schema['category'];

        if (!\in_array($category, self::VALID_CATEGORIES, true)) {
            throw ContentSystemException::elementSchemaInvalid(
                $schema['type'],
                \sprintf(
                    'Invalid category "%s". Must be one of: %s',
                    $category,
                    implode(', ', self::VALID_CATEGORIES)
                )
            );
        }
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function validateType(array $schema): void
    {
        $type = $schema['type'];

        // Type should follow pattern: Sw:{Domain}:{ElementType}
        if (!preg_match('/^[A-Z][a-zA-Z0-9]*:[A-Z][a-zA-Z0-9]*:[A-Z][a-zA-Z0-9]*$/', $type)) {
            throw ContentSystemException::elementSchemaInvalid(
                $type,
                'Type must follow pattern: Sw:{Domain}:{ElementType} (e.g., Sw:Product:Card)'
            );
        }
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function validateDataRequirements(array $schema): void
    {
        if (!isset($schema['data_requirements']) || !\is_array($schema['data_requirements'])) {
            return; // Optional field
        }

        foreach ($schema['data_requirements'] as $key => $requirement) {
            if (!\is_array($requirement)) {
                throw ContentSystemException::elementSchemaInvalid(
                    $schema['type'],
                    \sprintf('Data requirement "%s" must be an array', $key)
                );
            }

            $this->validateDataRequirement($schema['type'], $key, $requirement);
        }
    }

    /**
     * @param array<string, mixed> $requirement
     */
    private function validateDataRequirement(string $type, string $key, array $requirement): void
    {
        $requirementType = $requirement['type'] ?? 'entity';

        if (!\in_array($requirementType, self::VALID_REQUIREMENT_TYPES, true)) {
            throw ContentSystemException::elementSchemaInvalid(
                $type,
                \sprintf(
                    'Invalid requirement type "%s" for "%s". Must be one of: %s',
                    $requirementType,
                    $key,
                    implode(', ', self::VALID_REQUIREMENT_TYPES)
                )
            );
        }

        // Validate requirement type-specific fields
        match ($requirementType) {
            'entity' => $this->validateEntityRequirement($type, $key, $requirement),
            'service' => $this->validateServiceRequirement($type, $key, $requirement),
            'translation' => $this->validateTranslationRequirement($type, $key, $requirement),
            default => null,
        };
    }

    /**
     * @param array<string, mixed> $requirement
     */
    private function validateEntityRequirement(string $type, string $key, array $requirement): void
    {
        if (!isset($requirement['entity'])) {
            throw ContentSystemException::elementSchemaInvalid(
                $type,
                \sprintf('Entity requirement "%s" must specify "entity" field', $key)
            );
        }

        if (!isset($requirement['source']) && !isset($requirement['source_fallback'])) {
            throw ContentSystemException::elementSchemaInvalid(
                $type,
                \sprintf('Entity requirement "%s" must specify "source" or "source_fallback"', $key)
            );
        }
    }

    /**
     * @param array<string, mixed> $requirement
     */
    private function validateServiceRequirement(string $type, string $key, array $requirement): void
    {
        if (!isset($requirement['service'])) {
            throw ContentSystemException::elementSchemaInvalid(
                $type,
                \sprintf('Service requirement "%s" must specify "service" field', $key)
            );
        }
    }

    /**
     * @param array<string, mixed> $requirement
     */
    private function validateTranslationRequirement(string $type, string $key, array $requirement): void
    {
        if (!isset($requirement['source'])) {
            throw ContentSystemException::elementSchemaInvalid(
                $type,
                \sprintf('Translation requirement "%s" must specify "source" field', $key)
            );
        }
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function validatePropertiesSchema(array $schema): void
    {
        if (!isset($schema['properties_schema'])) {
            return; // Optional field
        }

        if (!\is_array($schema['properties_schema'])) {
            throw ContentSystemException::elementSchemaInvalid(
                $schema['type'],
                'properties_schema must be an array'
            );
        }

        // TODO: Implement JSON Schema validation if needed
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function validateSlotsSchema(array $schema): void
    {
        if (!isset($schema['slots_schema'])) {
            return; // Optional field
        }

        if (!\is_array($schema['slots_schema'])) {
            throw ContentSystemException::elementSchemaInvalid(
                $schema['type'],
                'slots_schema must be an array'
            );
        }
    }
}
