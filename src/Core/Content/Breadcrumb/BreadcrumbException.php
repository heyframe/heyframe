<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Breadcrumb;

use HeyFrame\Core\Content\Navigation\NavigationException;
use HeyFrame\Core\Content\Navigation\Exception\NavigationNotFoundException;
use HeyFrame\Core\Content\Product\Exception\ProductNotFoundException;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\HeyFrameHttpException;
use Symfony\Component\HttpFoundation\Response;

#[Package('inventory')]
class BreadcrumbException extends NavigationException
{
    public const BREADCRUMB_CATEGORY_NOT_FOUND = 'BREADCRUMB_CATEGORY_NOT_FOUND';

    public static function navigationNotFoundForProduct(string $productId): self
    {
        return new self(
            Response::HTTP_NO_CONTENT,
            self::BREADCRUMB_CATEGORY_NOT_FOUND,
            'The main navigation for product {{ productId }} is not found',
            ['productId' => $productId]
        );
    }

    public static function navigationNotFound(string $id): HeyFrameHttpException
    {
        return new NavigationNotFoundException($id);
    }

    public static function productNotFound(string $id): HeyFrameHttpException
    {
        return new ProductNotFoundException($id);
    }
}
