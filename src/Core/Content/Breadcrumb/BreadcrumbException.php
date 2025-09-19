<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Breadcrumb;

use HeyFrame\Core\Content\Category\CategoryException;
use HeyFrame\Core\Content\Category\Exception\CategoryNotFoundException;
use HeyFrame\Core\Content\Product\Exception\ProductNotFoundException;
use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('inventory')]
class BreadcrumbException extends CategoryException
{
    public const BREADCRUMB_CATEGORY_NOT_FOUND = 'BREADCRUMB_CATEGORY_NOT_FOUND';

    public static function categoryNotFoundForProduct(string $productId): self
    {
        return new self(
            Response::HTTP_NO_CONTENT,
            self::BREADCRUMB_CATEGORY_NOT_FOUND,
            'The main category for product {{ productId }} is not found',
            ['productId' => $productId]
        );
    }

    public static function categoryNotFound(string $id): HeyFrameHttpException
    {
        return new CategoryNotFoundException($id);
    }

    public static function productNotFound(string $id): HeyFrameHttpException
    {
        return new ProductNotFoundException($id);
    }
}
