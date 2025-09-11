<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Category;

use HeyFrame\Core\Content\Category\Exception\CategoryNotFoundException;
use HeyFrame\Core\Content\Cms\Exception\PageNotFoundException;
use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\HttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('discovery')]
class CategoryException extends HttpException
{
    public const SERVICE_CATEGORY_NOT_FOUND = 'CHECKOUT__SERVICE_CATEGORY_NOT_FOUND';
    public const FOOTER_CATEGORY_NOT_FOUND = 'CHECKOUT__FOOTER_CATEGORY_NOT_FOUND';
    public const AFTER_CATEGORY_NOT_FOUND = 'CONTENT__AFTER_CATEGORY_NOT_FOUND';

    public static function pageNotFound(string $pageId): HeyFrameHttpException
    {
        return new PageNotFoundException($pageId);
    }

    public static function categoryNotFound(string $id): HeyFrameHttpException
    {
        return new CategoryNotFoundException($id);
    }

    public static function serviceCategoryNotFoundForChannel(string $channelName): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::SERVICE_CATEGORY_NOT_FOUND,
            'Service category, for sales channel {{ channelName }}, is not set',
            ['channelName' => $channelName]
        );
    }

    public static function footerCategoryNotFoundForChannel(string $channelName): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::FOOTER_CATEGORY_NOT_FOUND,
            'Footer category, for sales channel {{ channelName }}, is not set',
            ['channelName' => $channelName]
        );
    }

    public static function afterCategoryNotFound(): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::AFTER_CATEGORY_NOT_FOUND,
            'Category to insert after not found.',
        );
    }
}
