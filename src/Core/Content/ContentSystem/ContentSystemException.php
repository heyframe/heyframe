<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem;

use HeyFrame\Core\Framework\HttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('discovery')]
class ContentSystemException extends HttpException
{
    // Soft errors (404 - Not Found)
    public const CONTENT_NOT_FOUND = 'CONTENT_SYSTEM__CONTENT_NOT_FOUND';
    public const ENTITY_NOT_FOUND = 'CONTENT_SYSTEM__ENTITY_NOT_FOUND';
    public const LAYOUT_ASSIGNMENT_NOT_FOUND = 'CONTENT_SYSTEM__LAYOUT_ASSIGNMENT_NOT_FOUND';

    // Hard errors (500 - Internal Server Error)
    public const LAYOUT_NOT_FOUND = 'CONTENT_SYSTEM__LAYOUT_NOT_FOUND';
    public const ROUTE_CONFIGURATION_ERROR = 'CONTENT_SYSTEM__ROUTE_CONFIGURATION_ERROR';
    public const RESOLUTION_FAILED = 'CONTENT_SYSTEM__RESOLUTION_FAILED';
    public const PAGE_BUILDING_FAILED = 'CONTENT_SYSTEM__PAGE_BUILDING_FAILED';
    public const HYDRATION_FAILED = 'CONTENT_SYSTEM__HYDRATION_FAILED';
    public const REFINEMENT_FAILED = 'CONTENT_SYSTEM__REFINEMENT_FAILED';

    // Type Registry errors (500)
    public const TYPE_NOT_REGISTERED = 'CONTENT_SYSTEM__TYPE_NOT_REGISTERED';
    public const HYDRATOR_NOT_FOUND = 'CONTENT_SYSTEM__HYDRATOR_NOT_FOUND';
    public const ELEMENT_MISSING_TYPE = 'CONTENT_SYSTEM__ELEMENT_MISSING_TYPE';
    public const ELEMENT_SCHEMA_INVALID = 'CONTENT_SYSTEM__ELEMENT_SCHEMA_INVALID';
    public const ELEMENT_SCHEMA_LOAD_FAILED = 'CONTENT_SYSTEM__ELEMENT_SCHEMA_LOAD_FAILED';

    // Context validation errors (500)
    public const INVALID_CONTEXT_TYPE = 'CONTENT_SYSTEM__INVALID_CONTEXT_TYPE';
    public const REQUIRED_CONTEXT_MISSING = 'CONTENT_SYSTEM__REQUIRED_CONTEXT_MISSING';

    // Value object validation errors (500)
    public const INVALID_MAP_KEY = 'CONTENT_SYSTEM__INVALID_MAP_KEY';
    public const INVALID_MAP_VALUE = 'CONTENT_SYSTEM__INVALID_MAP_VALUE';
    public const INVALID_DATA_REQUIREMENT = 'CONTENT_SYSTEM__INVALID_DATA_REQUIREMENT';

    // Data loader errors (500)
    public const DATA_LOADER_NOT_REGISTERED = 'CONTENT_SYSTEM__DATA_LOADER_NOT_REGISTERED';

    // Field serialization errors (500)
    public const INVALID_FIELD_TYPE = 'CONTENT_SYSTEM__INVALID_FIELD_TYPE';
    public const INVALID_FIELD_VALUE_TYPE = 'CONTENT_SYSTEM__INVALID_FIELD_VALUE_TYPE';

    // Legacy/Deprecated (kept for backwards compatibility)
    public const ROUTE_NOT_FOUND = 'CONTENT_SYSTEM__ROUTE_NOT_FOUND';
    public const ENTITY_NOT_RESOLVED = 'CONTENT_SYSTEM__ENTITY_NOT_RESOLVED';
    public const LAYOUT_NOT_RESOLVED = 'CONTENT_SYSTEM__LAYOUT_NOT_RESOLVED';
    public const INVALID_PARAMETER_BINDING = 'CONTENT_SYSTEM__INVALID_PARAMETER_BINDING';
    public const INVALID_RESOLVED_DATA = 'CONTENT_SYSTEM__INVALID_RESOLVED_DATA';
    public const UNSUPPORTED_FIELD_TYPE = 'CONTENT_SYSTEM__UNSUPPORTED_FIELD_TYPE';

    public static function routeNotFound(string $pathInfo): self
    {
        return new self(
            Response::HTTP_NOT_FOUND,
            self::ROUTE_NOT_FOUND,
            'No content route found for path "{{ pathInfo }}"',
            ['pathInfo' => $pathInfo]
        );
    }

    public static function entityNotResolved(string $parameter, string $value): self
    {
        return new self(
            Response::HTTP_NOT_FOUND,
            self::ENTITY_NOT_RESOLVED,
            'Entity for parameter "{{ parameter }}" with value "{{ value }}" could not be resolved',
            ['parameter' => $parameter, 'value' => $value]
        );
    }

    public static function layoutNotResolved(string $entityType, string $entityId): self
    {
        return new self(
            Response::HTTP_NOT_FOUND,
            self::LAYOUT_NOT_RESOLVED,
            'Layout for entity type "{{ entityType }}" with ID "{{ entityId }}" could not be resolved',
            ['entityType' => $entityType, 'entityId' => $entityId]
        );
    }

    public static function invalidParameterBinding(string $routeName): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::INVALID_PARAMETER_BINDING,
            'Invalid parameter binding configuration for route "{{ routeName }}"',
            ['routeName' => $routeName]
        );
    }

    public static function invalidResolvedData(string $message): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::INVALID_RESOLVED_DATA,
            'Invalid resolved data: {{ message }}',
            ['message' => $message]
        );
    }

    public static function unsupportedFieldType(string $fieldClass): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::UNSUPPORTED_FIELD_TYPE,
            'Unsupported field type: {{ fieldClass }}',
            ['fieldClass' => $fieldClass]
        );
    }

    // SOFT ERRORS (404 - Not Found)

    public static function contentNotFound(string $pathInfo): self
    {
        return new self(
            Response::HTTP_NOT_FOUND,
            self::CONTENT_NOT_FOUND,
            self::$couldNotFindMessage,
            ['entity' => 'content', 'field' => 'path', 'value' => $pathInfo]
        );
    }

    public static function entityNotFound(string $entityType, string $identifier, string $matchField): self
    {
        return new self(
            Response::HTTP_NOT_FOUND,
            self::ENTITY_NOT_FOUND,
            self::$couldNotFindMessage,
            ['entity' => $entityType, 'field' => $matchField, 'value' => $identifier]
        );
    }

    public static function layoutAssignmentNotFound(string $entityType, string $entityId, string $channelId): self
    {
        return new self(
            Response::HTTP_NOT_FOUND,
            self::LAYOUT_ASSIGNMENT_NOT_FOUND,
            'No layout assignment found for {{ entityType }} "{{ entityId }}" in sales channel "{{ channelId }}"',
            ['entityType' => $entityType, 'entityId' => $entityId, 'channelId' => $channelId]
        );
    }

    // HARD ERRORS (500 - Internal Server Error)

    public static function layoutNotFound(string $layoutId): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::LAYOUT_NOT_FOUND,
            'Content layout with ID "{{ layoutId }}" does not exist. This indicates a configuration error.',
            ['layoutId' => $layoutId]
        );
    }

    public static function routeConfigurationError(string $routeName, string $reason): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::ROUTE_CONFIGURATION_ERROR,
            'Content route "{{ routeName }}" has invalid configuration: {{ reason }}',
            ['routeName' => $routeName, 'reason' => $reason]
        );
    }

    public static function resolutionFailed(string $routeName, string $reason, ?\Throwable $previous = null): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::RESOLUTION_FAILED,
            'Entity resolution failed for route "{{ routeName }}": {{ reason }}',
            ['routeName' => $routeName, 'reason' => $reason],
            $previous
        );
    }

    public static function pageBuildingFailed(string $layoutId, string $reason, ?\Throwable $previous = null): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::PAGE_BUILDING_FAILED,
            'Page building failed for layout "{{ layoutId }}": {{ reason }}',
            ['layoutId' => $layoutId, 'reason' => $reason],
            $previous
        );
    }

    public static function hydrationFailed(string $reason, ?\Throwable $previous = null): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::HYDRATION_FAILED,
            'Entity hydration failed: {{ reason }}',
            ['reason' => $reason],
            $previous
        );
    }

    public static function refinementFailed(string $refinerClass, string $reason, ?\Throwable $previous = null): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::REFINEMENT_FAILED,
            'Layout refinement failed in {{ refinerClass }}: {{ reason }}',
            ['refinerClass' => $refinerClass, 'reason' => $reason],
            $previous
        );
    }

    // TYPE REGISTRY ERRORS (500 - Internal Server Error)

    public static function typeNotRegistered(string $typeId): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::TYPE_NOT_REGISTERED,
            'Content element type "{{ typeId }}" is not registered. Register the type in the ContentElementTypeRegistry.',
            ['typeId' => $typeId]
        );
    }

    public static function hydratorNotFound(string $typeId, string $hydratorClass): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::HYDRATOR_NOT_FOUND,
            'Hydrator "{{ hydratorClass }}" for content element type "{{ typeId }}" not found. Ensure the hydrator is registered as a tagged service.',
            ['typeId' => $typeId, 'hydratorClass' => $hydratorClass]
        );
    }

    public static function elementMissingType(): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::ELEMENT_MISSING_TYPE,
            'Content element is missing required "type" property. All elements must declare their type.',
            []
        );
    }

    public static function elementSchemaInvalid(string $typeId, string $reason): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::ELEMENT_SCHEMA_INVALID,
            'Element type schema "{{ typeId }}" is invalid: {{ reason }}',
            ['typeId' => $typeId, 'reason' => $reason]
        );
    }

    public static function elementSchemaLoadFailed(string $file, string $reason, ?\Throwable $previous = null): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::ELEMENT_SCHEMA_LOAD_FAILED,
            'Failed to load element type schema from "{{ file }}": {{ reason }}',
            ['file' => $file, 'reason' => $reason],
            $previous
        );
    }

    // CONTEXT VALIDATION ERRORS (500 - Internal Server Error)

    public static function invalidContextType(string $contextType, string $expected, string $actual): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::INVALID_CONTEXT_TYPE,
            '{{ contextType }} context expected {{ expected }}, got {{ actual }}',
            ['contextType' => $contextType, 'expected' => $expected, 'actual' => $actual]
        );
    }

    public static function requiredContextMissing(string $contextKey): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::REQUIRED_CONTEXT_MISSING,
            'Required context "{{ contextKey }}" is missing',
            ['contextKey' => $contextKey]
        );
    }

    // VALUE OBJECT VALIDATION ERRORS (500 - Internal Server Error)

    public static function invalidMapKey(string $mapType, string $actualType): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::INVALID_MAP_KEY,
            '{{ mapType }} key must be string, got {{ actualType }}',
            ['mapType' => $mapType, 'actualType' => $actualType]
        );
    }

    public static function invalidMapValue(string $mapType, string $key, string $expectedType, string $actualType): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::INVALID_MAP_VALUE,
            '{{ mapType }} value for "{{ key }}" must be {{ expectedType }}, got {{ actualType }}',
            ['mapType' => $mapType, 'key' => $key, 'expectedType' => $expectedType, 'actualType' => $actualType]
        );
    }

    public static function invalidDataRequirement(string $actualType): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::INVALID_DATA_REQUIREMENT,
            'Requirement must be array, {{ actualType }} given',
            ['actualType' => $actualType]
        );
    }

    public static function dataLoaderNotRegistered(string $requirementType, string $elementType, string $elementId): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::DATA_LOADER_NOT_REGISTERED,
            'Data loader for requirement type "{{ requirementType }}" not registered. Element type: "{{ elementType }}", element ID: "{{ elementId }}"',
            ['requirementType' => $requirementType, 'elementType' => $elementType, 'elementId' => $elementId]
        );
    }

    // FIELD SERIALIZATION ERRORS (500 - Internal Server Error)

    public static function invalidFieldType(string $expectedClass, string $actualClass): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::INVALID_FIELD_TYPE,
            'Expected field of type {{ expectedClass }}, got {{ actualClass }}',
            ['expectedClass' => $expectedClass, 'actualClass' => $actualClass]
        );
    }

    public static function invalidFieldValueType(string $fieldName, string $expectedType, string $actualType): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::INVALID_FIELD_VALUE_TYPE,
            'Field {{ fieldName }} expected {{ expectedType }}, got {{ actualType }}',
            ['fieldName' => $fieldName, 'expectedType' => $expectedType, 'actualType' => $actualType]
        );
    }
}
