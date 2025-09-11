<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Seo;

use HeyFrame\Core\Content\Seo\Exception\InvalidTemplateException;
use HeyFrame\Core\Content\Seo\Exception\NoEntitiesForPreviewException;
use HeyFrame\Core\Content\Seo\Exception\SeoUrlRouteNotFoundException;
use HeyFrame\Core\Framework\Api\Exception\InvalidChannelIdException;
use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\HttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('inventory')]
class SeoException extends HttpException
{
    public const SALES_CHANNEL_ID_PARAMETER_IS_MISSING = 'FRAMEWORK__SALES_CHANNEL_ID_PARAMETER_IS_MISSING';
    public const TEMPLATE_PARAMETER_IS_MISSING = 'FRAMEWORK__TEMPLATE_PARAMETER_IS_MISSING';
    public const ROUTE_NAME_PARAMETER_IS_MISSING = 'FRAMEWORK__ROUTE_NAME_PARAMETER_IS_MISSING';
    public const ENTITY_NAME_PARAMETER_IS_MISSING = 'FRAMEWORK__ENTITY_NAME_PARAMETER_IS_MISSING';
    public const SALES_CHANNEL_NOT_FOUND = 'FRAMEWORK__SALES_CHANNEL_NOT_FOUND';

    public static function invalidChannelId(string $channelId): HeyFrameHttpException
    {
        return new InvalidChannelIdException($channelId);
    }

    public static function channelIdParameterIsMissing(): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::SALES_CHANNEL_ID_PARAMETER_IS_MISSING,
            'Parameter "channelId" is missing.',
        );
    }

    public static function templateParameterIsMissing(): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::TEMPLATE_PARAMETER_IS_MISSING,
            'Parameter "template" is missing.',
        );
    }

    public static function entityNameParameterIsMissing(): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::ENTITY_NAME_PARAMETER_IS_MISSING,
            'Parameter "entityName" is missing.',
        );
    }

    public static function routeNameParameterIsMissing(): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::ROUTE_NAME_PARAMETER_IS_MISSING,
            'Parameter "routeName" is missing.',
        );
    }

    public static function channelNotFound(string $channelId): self
    {
        return new self(
            Response::HTTP_NOT_FOUND,
            self::SALES_CHANNEL_NOT_FOUND,
            self::$couldNotFindMessage,
            ['entity' => 'sales channel', 'field' => 'id', 'value' => $channelId]
        );
    }

    public static function seoUrlRouteNotFound(string $routeName): HeyFrameHttpException
    {
        return new SeoUrlRouteNotFoundException($routeName);
    }

    public static function noEntitiesForPreview(string $entityName, string $routeName): HeyFrameHttpException
    {
        return new NoEntitiesForPreviewException($entityName, $routeName);
    }

    public static function invalidTemplate(string $message): HeyFrameHttpException
    {
        return new InvalidTemplateException($message);
    }
}
