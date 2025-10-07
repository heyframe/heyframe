<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem;

use HeyFrame\Core\Framework\HttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('discovery')]
class ContentSystemException extends HttpException
{
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
}
